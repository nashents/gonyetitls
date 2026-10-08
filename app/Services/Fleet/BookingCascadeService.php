<?php

namespace App\Services\Fleet;

use App\Models\Booking;
use App\Models\Hour;
use App\Models\Inspection;
use App\Models\Mileage;
use App\Models\Ticket;
use App\Services\Sage\SageSyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Carries a booking edit through to everything approval copied the booking
 * into - its inspection, its ticket and the records hanging off the ticket
 * (requisitions, dispatches, bills and their ledger lines, ...) - so a
 * corrected horse/vehicle/trailer, customer or service type doesn't leave
 * the job's costs and history on the old values.
 */
class BookingCascadeService
{
    public const EQUIPMENT = ['horse_id', 'vehicle_id', 'trailer_id', 'asset_id'];

    /** Ticket-linked tables that carry the equipment the job was for. */
    protected const TICKET_TABLES = [
        'inventory_requisitions', 'inventory_dispatches', 'ticket_inventories',
        'dispatches', 'ticket_requests', 'bills', 'cash_flows',
    ];

    /** What a booking looked like before an edit - pass to sync() after it. */
    public function snapshot(Booking $booking): array
    {
        $snapshot = $booking->only(array_merge(self::EQUIPMENT, ['transporter_id', 'customer_id', 'service_type_id']));
        $snapshot['mechanics'] = $booking->employees()->pluck('employees.id')->map(fn ($id) => (int) $id)->all();

        return $snapshot;
    }

    public function sync(Booking $booking, array $before): void
    {
        $booking->refresh();

        $after = $this->snapshot($booking);
        $oldEquipment = $this->primaryEquipment($before);
        $newEquipment = $this->primaryEquipment($after);
        $equipmentChanged = $oldEquipment !== $newEquipment;
        $transporterChanged = (int) ($before['transporter_id'] ?? 0) !== (int) ($after['transporter_id'] ?? 0);

        $inspections = Inspection::where('booking_id', $booking->id)->get();
        $tickets = Ticket::where('booking_id', $booking->id)->get();
        $equipmentColumns = array_fill_keys(self::EQUIPMENT, null);
        foreach (self::EQUIPMENT as $column) {
            $equipmentColumns[$column] = $booking->{$column};
        }

        foreach ($inspections as $inspection) {
            $inspection->forceFill($equipmentColumns + [
                'service_type_id' => $booking->service_type_id,
            ])->save();
            $this->syncMechanics($inspection, $before['mechanics'], $after['mechanics']);
        }

        foreach ($tickets as $ticket) {
            $ticket->forceFill($equipmentColumns + [
                'customer_id'     => $booking->customer_id,
                'service_type_id' => $booking->service_type_id,
                'in_date'         => $booking->in_date,
                'in_time'         => $booking->in_time,
                'odometer'        => $booking->odometer,
                'hours'           => $booking->hours,
                'station'         => $booking->station?->name ?? $ticket->station,
            ])->save();
            $this->syncMechanics($ticket, $before['mechanics'], $after['mechanics']);
        }

        $ticketIds = $tickets->pluck('id')->all();
        if ($ticketIds && ($equipmentChanged || $transporterChanged)) {
            $this->moveTicketRecords($ticketIds, $oldEquipment, $newEquipment, $before['transporter_id'] ?? null, $after['transporter_id'] ?? null);
        }

        $this->syncReadings($booking);

        if ($equipmentChanged && $booking->authorization === 'approved' && (int) $booking->status === 1) {
            $this->moveServiceFlag($booking, $oldEquipment, $newEquipment);
        }

        $sageRelevant = $equipmentChanged
            || (int) ($before['customer_id'] ?? 0) !== (int) $booking->customer_id
            || (int) ($before['service_type_id'] ?? 0) !== (int) $booking->service_type_id;

        if ($sageRelevant && $booking->authorization === 'approved') {
            foreach ($tickets as $ticket) {
                DB::afterCommit(function () use ($ticket) {
                    try {
                        app(SageSyncService::class)->syncJobCard($ticket->fresh());
                    } catch (\Throwable $e) {
                        Log::warning("Sage job-card resync after booking edit failed for ticket #{$ticket->id}: " . $e->getMessage());
                    }
                });
            }
        }
    }

    /** [column, id] of the one piece of equipment the booking is for, or null. */
    protected function primaryEquipment(array $values): ?array
    {
        foreach (self::EQUIPMENT as $column) {
            if (!empty($values[$column])) {
                return [$column, (int) $values[$column]];
            }
        }

        return null;
    }

    /**
     * Mechanics added/removed on the booking are added/removed on the
     * inspection or ticket too; anyone assigned there directly is left alone.
     */
    protected function syncMechanics($record, array $before, array $after): void
    {
        $removed = array_diff($before, $after);
        $added = array_diff($after, $before);

        if ($removed) {
            $record->employees()->detach($removed);
        }
        if ($added) {
            $record->employees()->syncWithoutDetaching($added);
        }
    }

    /**
     * Rows on the job's tickets that were for the old equipment move to the
     * new one (rows for something else, e.g. a trailer part on a horse job,
     * stay as they are), and the bills' ledger lines keep the same dimension.
     */
    protected function moveTicketRecords(array $ticketIds, ?array $old, ?array $new, $oldTransporter, $newTransporter): void
    {
        $billIds = DB::table('bills')->whereIn('ticket_id', $ticketIds)->pluck('id')->all();

        foreach (self::TICKET_TABLES as $table) {
            $this->moveRows(DB::table($table)->whereIn('ticket_id', $ticketIds), $table, $old, $new, $oldTransporter, $newTransporter);
        }

        if ($billIds) {
            $entryIds = DB::table('journal_entries')->whereIn('bill_id', $billIds)->pluck('id')->all();
            if ($entryIds) {
                $this->moveRows(DB::table('journal_entry_lines')->whereIn('journal_entry_id', $entryIds), 'journal_entry_lines', $old, $new, $oldTransporter, $newTransporter);
            }
        }
    }

    protected function moveRows($query, string $table, ?array $old, ?array $new, $oldTransporter, $newTransporter): void
    {
        $columns = Schema::getColumnListing($table);

        if ($old && $old !== $new && in_array($old[0], $columns, true)) {
            $changes = [$old[0] => null];
            if ($new && in_array($new[0], $columns, true)) {
                $changes[$new[0]] = $new[1];
            }
            (clone $query)->where($old[0], $old[1])->update($changes);
        }

        if ($oldTransporter && (int) $oldTransporter !== (int) $newTransporter && in_array('transporter_id', $columns, true)) {
            (clone $query)->where('transporter_id', $oldTransporter)->update(['transporter_id' => $newTransporter ?: null]);
        }
    }

    /** The booking's mileage / hours log rows follow its equipment and readings. */
    protected function syncReadings(Booking $booking): void
    {
        $equipment = [
            'horse_id'   => $booking->horse_id,
            'vehicle_id' => $booking->vehicle_id,
            'trailer_id' => $booking->trailer_id,
        ];

        foreach (Mileage::where('booking_id', $booking->id)->get() as $mileage) {
            $mileage->forceFill($equipment + ['mileage' => $booking->odometer, 'date' => $booking->in_date])->save();
        }

        foreach (Hour::where('booking_id', $booking->id)->get() as $hour) {
            $hour->forceFill($equipment + ['hours' => $booking->hours, 'date' => $booking->in_date])->save();
        }
    }

    /**
     * The new equipment is the one in the workshop now (flagged and its
     * readings bumped, as approval does); the old one is freed unless
     * another open approved booking still has it in.
     */
    protected function moveServiceFlag(Booking $booking, ?array $old, ?array $new): void
    {
        $models = [
            'horse_id'   => \App\Models\Horse::class,
            'vehicle_id' => \App\Models\Vehicle::class,
            'trailer_id' => \App\Models\Trailer::class,
        ];

        if ($old && isset($models[$old[0]])) {
            $stillIn = Booking::where($old[0], $old[1])
                ->where('id', '!=', $booking->id)
                ->where('authorization', 'approved')
                ->where('status', 1)
                ->exists();

            $oldModel = $models[$old[0]]::find($old[1]);
            if ($oldModel && !$stillIn) {
                $oldModel->service = 0;
                $oldModel->save();
            }
        }

        if ($new && isset($models[$new[0]])) {
            $newModel = $models[$new[0]]::find($new[1]);
            if ($newModel) {
                $newModel->service = 1;
                if (is_numeric($booking->odometer) && $booking->odometer > (float) $newModel->mileage) {
                    $newModel->mileage = $booking->odometer;
                }
                if ($new[0] !== 'trailer_id' && is_numeric($booking->hours) && $booking->hours > (float) $newModel->hours) {
                    $newModel->hours = $booking->hours;
                }
                $newModel->save();
            }
        }
    }
}
