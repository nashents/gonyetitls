<?php

namespace App\Services\Trips;

use App\Mail\PendingNotificationEmails;
use App\Models\DeliveryNote;
use App\Models\Driver;
use App\Models\Expense;
use App\Models\Horse;
use App\Models\Notification;
use App\Models\Trailer;
use App\Models\TransportOrder;
use App\Models\Trip;
use App\Models\TripDestination;
use App\Models\TripExpense;
use App\Models\TripOrigin;
use App\Models\TripTransportOrder;
use App\Models\UnitsOfMeasure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Bulk schedule trips (Trips > Bulk Schedule): a row per truck, either all on
 * one job or each with its own customer/cargo/route/rate. Each row becomes an
 * ordinary pending trip, saved the way Trips\Create::store() saves one with no
 * transport order attached: its own auto-created transport order (carrying the
 * trip ref) with origin/destination legs, the trip <-> order link, a delivery
 * note, trailers, the "Transporter Payment" expense when that row has
 * transporter freight, and the "Trip Authorization" (Before) notifications.
 * All rows save or none do.
 */
class BulkTripScheduleService
{
    // Statuses where Trips\Create leaves the truck, driver and trailers available.
    const AVAILABLE_STATUSES = ['Scheduled', 'Offloaded', 'Cancelled'];

    /**
     * @param array $trips one fully resolved trip each: customer_id, transporter_id, trip_type_id, cargo_id, cargo_type,
     *                     consignee_id, consignor_id, currency_id, exchange_rate, from, to, loading_point_id,
     *                     offloading_point_id, distance, units_of_measure_id, freight_calculation, rate, cargo_details,
     *                     trip_status, transporter_agreement, transporter_rate, horse_id, driver_id, trailer_ids,
     *                     trip_ref, start_date, weight, quantity, litreage, comments,
     *                     expenses (expense_id, funded_by, category, payment_method_id, currency_id, exchange_rate, vendor_id, amount)
     * @return Collection<int, Trip>
     */
    public function create(array $trips, $company): Collection
    {
        $trips = DB::transaction(function () use ($trips, $company) {
            $created = collect();

            foreach ($trips as $trip) {
                $created->push($this->createTrip($trip, $company));
            }

            return $created;
        });

        $this->notify($trips, $company);

        return $trips;
    }

    /** Same rules as Trips\Create::calculateFreight(): measure is the row's weight (Solid) or litreage (Liquid). */
    public static function freight($method, $rate, $measure, $distance): float
    {
        if (! is_numeric($rate)) {
            return 0;
        }

        $measure = is_numeric($measure) ? (float) $measure : 0;
        $distance = is_numeric($distance) ? (float) $distance : 0;

        switch ($method) {
            case 'rate_weight':
                return round($rate * $measure, 2);
            case 'rate_distance':
                return round($rate * $distance, 2);
            case 'rate_weight_distance':
                return round($rate * $measure * $distance, 2);
            default:
                return round((float) $rate, 2);
        }
    }

    public static function measure(array $t, $cargoType)
    {
        return $cargoType === 'Liquid' ? ($t['litreage'] ?? null) : ($t['weight'] ?? null);
    }

    private function createTrip(array $t, $company): Trip
    {
        $userId = Auth::id();
        $tripRef = trim((string) ($t['trip_ref'] ?? '')) ?: null;
        $isLiquid = $t['cargo_type'] === 'Liquid';
        $exchangeRate = is_numeric($t['exchange_rate'] ?? null) ? (float) $t['exchange_rate'] : null;
        $uomId = ! empty($t['units_of_measure_id']) && UnitsOfMeasure::whereKey($t['units_of_measure_id'])->exists()
            ? $t['units_of_measure_id'] : null;

        $weight = is_numeric($t['weight'] ?? null) ? $t['weight'] : null;
        $quantity = is_numeric($t['quantity'] ?? null) ? $t['quantity'] : null;
        $litreage = $isLiquid && is_numeric($t['litreage'] ?? null) ? $t['litreage'] : null;

        $measure = static::measure($t, $t['cargo_type']);
        $freight = static::freight($t['freight_calculation'], $t['rate'], $measure, $t['distance']);
        $transporterFreight = $t['transporter_agreement']
            ? static::freight($t['freight_calculation'], $t['transporter_rate'], $measure, $t['distance'])
            : 0;
        $exchangeFreight = $exchangeRate && $freight > 0 ? round($exchangeRate * $freight, 2) : null;
        $exchangeTransporterFreight = $exchangeRate && $transporterFreight > 0 ? round($exchangeRate * $transporterFreight, 2) : null;
        $turnover = $company->currency_id == $t['currency_id'] ? $freight : $exchangeFreight;
        $calculationMeasurement = $isLiquid ? 'litreage_at_20' : ($t['cargo_type'] === 'Solid' ? 'weight' : null);

        $transportOrder = new TransportOrder;
        $transportOrder->transport_order_number = $this->number(TransportOrder::class, 'TO', $company);
        $transportOrder->custom_ref = $tripRef;
        $transportOrder->user_id = $userId;
        $transportOrder->company_id = $company->id;
        $transportOrder->with_customer_rates = 'custom';
        $transportOrder->with_transporter_rates = 'custom';
        $transportOrder->customer_id = $t['customer_id'];
        $transportOrder->consignee_id = $t['consignee_id'] ?: null;
        $transportOrder->consignor_id = $t['consignor_id'] ?: null;
        $transportOrder->freight_calculation = $t['freight_calculation'];
        $transportOrder->calculation_measurement = $calculationMeasurement;
        $transportOrder->currency_id = $t['currency_id'];
        $transportOrder->cargo_id = $t['cargo_id'];
        $transportOrder->trip_type_id = $t['trip_type_id'];
        $transportOrder->from = $t['from'];
        $transportOrder->to = $t['to'];
        $transportOrder->loading_point_id = $t['loading_point_id'] ?: null;
        $transportOrder->offloading_point_id = $t['offloading_point_id'] ?: null;
        $transportOrder->start_date = $t['start_date'];
        $transportOrder->cargo_details = $t['cargo_details'] ?: null;
        $transportOrder->multiple_destinations = false;
        $transportOrder->quantity = $quantity;
        $transportOrder->litreage = $litreage;
        $transportOrder->units_of_measure_id = $uomId;
        $transportOrder->weight = $weight;
        $transportOrder->status = 'Pending';
        $transportOrder->rate = $t['rate'];
        $transportOrder->freight = $freight;
        $transportOrder->transporter_rate = $t['transporter_agreement'] ? $t['transporter_rate'] : 0;
        $transportOrder->transporter_freight = $transporterFreight;
        $transportOrder->exchange_rate = $exchangeRate;
        $transportOrder->exchange_customer_freight = $exchangeFreight;
        $transportOrder->distance = $t['distance'] ?: null;
        $transportOrder->save();

        $leg = [
            'transport_order_id' => $transportOrder->id,
            'user_id' => $userId,
            'weight' => $weight,
            'quantity' => $quantity,
            'units_of_measure_id' => $uomId,
            'litreage' => $litreage,
            'rate' => $t['rate'],
            'freight' => $freight,
        ];
        $origin = TripOrigin::create($leg + ['destination_id' => $t['from'], 'loading_point_id' => $t['loading_point_id'] ?: null]);
        $destination = TripDestination::create($leg + ['destination_id' => $t['to'], 'offloading_point_id' => $t['offloading_point_id'] ?: null]);

        $trailerIds = Trailer::whereIn('id', (array) ($t['trailer_ids'] ?? []))->pluck('id')->all();

        $trip = new Trip;
        $trip->trip_number = $this->number(Trip::class, 'T', $company);
        $trip->manifest_number = $this->manifestNumber();
        $trip->trip_ref = $tripRef;
        $trip->user_id = $userId;
        $trip->company_id = $company->id;
        $trip->horse_id = $t['horse_id'];
        $trip->driver_id = $t['driver_id'];
        $trip->transporter_id = $t['transporter_id'];
        $trip->transporter_agreement = (bool) $t['transporter_agreement'];
        $trip->with_trailer = count($trailerIds) > 0;
        $trip->trip_status = $t['trip_status'];
        $trip->trip_status_date = $t['start_date'];
        $trip->start_date = $t['start_date'];
        $trip->comments = $t['comments'] ?? null;
        $trip->currency_id = $t['currency_id'];
        $trip->attach_transport_order = false;
        $trip->emptyrun_origin = false;
        $trip->emptyrun_destination = false;
        $trip->with_customer_rates = 'custom';
        $trip->with_transporter_rates = 'custom';
        $trip->customer_id = $t['customer_id'];
        $trip->consignee_id = $t['consignee_id'] ?: null;
        $trip->consignor_id = $t['consignor_id'] ?: null;
        $trip->freight_calculation = $t['freight_calculation'];
        $trip->calculation_measurement = $calculationMeasurement;
        $trip->cargo_id = $t['cargo_id'];
        $trip->cargo_details = $t['cargo_details'] ?: null;
        $trip->trip_type_id = $t['trip_type_id'];
        $trip->from = $t['from'];
        $trip->to = $t['to'];
        $trip->loading_point_id = $t['loading_point_id'] ?: null;
        $trip->offloading_point_id = $t['offloading_point_id'] ?: null;
        $trip->multiple_destinations = false;
        $trip->rate = $t['rate'];
        $trip->transporter_rate = $t['transporter_agreement'] ? $t['transporter_rate'] : 0;
        $trip->quantity = $quantity;
        $trip->units_of_measure_id = $uomId;
        $trip->litreage = $litreage;
        $trip->weight = $weight;
        $trip->freight = $freight;
        $trip->transporter_freight = $transporterFreight;
        $trip->exchange_rate = $exchangeRate;
        $trip->exchange_customer_freight = $exchangeFreight;
        $trip->exchange_transporter_freight = $exchangeTransporterFreight;
        $trip->turnover = $turnover;
        $trip->distance = $t['distance'] ?: null;
        $trip->save();

        if ($trailerIds) {
            $trip->trailers()->sync($trailerIds);
        }

        $tripTransportOrder = TripTransportOrder::firstOrNew(['trip_id' => $trip->id, 'transport_order_id' => $transportOrder->id]);
        $tripTransportOrder->tto_number = $this->number(TripTransportOrder::class, 'TO', $company);
        $tripTransportOrder->allocated_quantity = $quantity;
        $tripTransportOrder->allocated_weight = $weight;
        $tripTransportOrder->allocated_litreage = $litreage;
        $tripTransportOrder->allocated_freight = $freight;
        $tripTransportOrder->allocated_rate = $t['rate'];
        $tripTransportOrder->units_of_measure_id = $uomId;
        $tripTransportOrder->currency_id = $t['currency_id'];
        $tripTransportOrder->exchange_rate = $exchangeRate;
        $tripTransportOrder->exchange_amount = $exchangeFreight;
        $tripTransportOrder->save();

        $origin->update(['trip_id' => $trip->id, 'trip_transport_order_id' => $tripTransportOrder->id]);
        $destination->update(['trip_id' => $trip->id, 'trip_transport_order_id' => $tripTransportOrder->id]);

        $deliveryNote = new DeliveryNote;
        $deliveryNote->user_id = $userId;
        $deliveryNote->trip_id = $trip->id;
        $deliveryNote->transport_order_id = $transportOrder->id;
        $deliveryNote->trip_transport_order_id = $tripTransportOrder->id;
        $deliveryNote->units_of_measure_id = $uomId;
        $deliveryNote->distance = $trip->distance;
        if ($isLiquid) {
            $deliveryNote->loaded_litreage = $litreage;
        } else {
            $deliveryNote->loaded_quantity = $quantity;
        }
        $deliveryNote->loaded_weight = $weight;
        $deliveryNote->save();

        if (in_array($t['trip_status'], self::AVAILABLE_STATUSES, true)) {
            Horse::whereKey($trip->horse_id)->update(['status' => 1]);
            Driver::whereKey($trip->driver_id)->update(['status' => 1]);
            if ($trailerIds) {
                Trailer::whereIn('id', $trailerIds)->update(['status' => 1]);
            }
        }

        if ($t['transporter_agreement']) {
            $tripExpense = new TripExpense;
            $tripExpense->user_id = $userId;
            $tripExpense->trip_id = $trip->id;
            $tripExpense->transporter_id = $trip->transporter_id;
            $tripExpense->expense_id = Expense::where('name', 'Transporter Payment')->value('id');
            $tripExpense->currency_id = $trip->currency_id;
            $tripExpense->category = 'Self';
            $tripExpense->amount = $transporterFreight;
            $tripExpense->exchange_rate = $exchangeRate ?? 1;
            $tripExpense->exchange_amount = $exchangeTransporterFreight ?? 0;
            $tripExpense->save();
        }

        foreach ($t['expenses'] ?? [] as $line) {
            $this->addExpense($trip, $line, $t['start_date'], $company);
        }

        $this->recalculateTrip($trip, $company);

        return $trip;
    }

    // Same row Trips\Create::store() writes for a ticked trip expense. Funded by: 0 company, 1 customer
    // (settles against the trip customer at approval), 2 transporter (record only, category Transporter).
    private function addExpense(Trip $trip, array $line, $date, $company): void
    {
        $fundedBy = (string) ($line['funded_by'] ?? '0');
        $foreign = $line['currency_id'] != $company->currency_id;
        $rate = $foreign && is_numeric($line['exchange_rate'] ?? null) ? (float) $line['exchange_rate'] : null;

        $tripExpense = new TripExpense;
        $tripExpense->user_id = Auth::id();
        $tripExpense->trip_id = $trip->id;
        $tripExpense->expense_id = $line['expense_id'];
        $tripExpense->currency_id = $line['currency_id'];
        $tripExpense->vendor_id = $line['vendor_id'] ?: null;
        $tripExpense->payment_method_id = $line['payment_method_id'] ?: null;
        $tripExpense->category = $fundedBy === '2' ? 'Transporter' : $line['category'];
        if ($fundedBy === '1') {
            $tripExpense->supplied_by_customer = true;
            $tripExpense->customer_id = $trip->customer_id;
        }
        $tripExpense->funded_by_transporter = $fundedBy === '2';
        $tripExpense->amount = $line['amount'];
        $tripExpense->exchange_rate = $rate;
        $tripExpense->exchange_amount = $rate ? round($rate * $line['amount'], 2) : 0;
        $tripExpense->date = $date;
        $tripExpense->save();
    }

    // Same figures Trips\Create::recalculateExpenses() writes: cost of sales = the trip's "Self" expenses in company currency.
    private function recalculateTrip(Trip $trip, $company): void
    {
        $costOfSales = TripExpense::where('trip_id', $trip->id)->where('category', 'Self')->get()
            ->sum(function (TripExpense $expense) use ($company) {
                $value = $expense->currency_id == $company->currency_id ? $expense->amount : $expense->exchange_amount;

                return is_numeric($value) ? (float) $value : 0;
            });

        $trip->cost_of_sales = $costOfSales;
        $turnover = $trip->turnover;

        if ($costOfSales > 0 && $turnover > 0) {
            $trip->net_profit = $turnover - $costOfSales;
            $profitable = $trip->net_profit > 0;
            $trip->markup_percentage = $profitable ? ($trip->net_profit / $costOfSales) * 100 : 0;
            $trip->net_profit_percentage = $profitable ? ($trip->net_profit / $turnover) * 100 : 0;
        } else {
            $trip->net_profit_percentage = 100;
            $trip->markup_percentage = 100;
        }

        $trip->update();
    }

    // Sent after commit, and a mail failure never undoes the trips.
    private function notify(Collection $trips, $company): void
    {
        $notifications = Notification::where('when', 'before')->where('category', 'Trip Authorization')->where('status', 1)->get();

        foreach ($notifications as $notification) {
            $email = $notification->email ?? optional($notification->employee)->email;
            if (! $email) {
                continue;
            }
            foreach ($trips as $trip) {
                try {
                    Mail::to($email)->send(new PendingNotificationEmails($company, $notification, $trip));
                } catch (\Throwable $e) {
                    Log::warning('Bulk trip notification failed', ['trip_id' => $trip->id, 'error' => $e->getMessage()]);
                }
            }
        }
    }

    // Same numbering as Trips\Create: company initials + prefix + next id.
    private function number(string $model, string $prefix, $company): string
    {
        $words = explode(' ', (string) $company->name);
        $initials = isset($words[1][0]) ? $words[0][0].$words[1][0] : ($words[0][0] ?? '');

        $lastId = $model::withTrashed()->max('id') ?? 0;

        return $initials.$prefix.str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
    }

    // Same format as Trips\Create::manifestNumber().
    private function manifestNumber(): string
    {
        $lastId = Trip::withTrashed()->max('id') ?? 0;

        return 'MAN'.str_pad($lastId + 1, 5, '0', STR_PAD_LEFT).'/'.now()->format('dmY');
    }
}
