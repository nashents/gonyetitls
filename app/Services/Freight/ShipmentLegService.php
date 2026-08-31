<?php

namespace App\Services\Freight;

use App\Models\ShipmentLeg;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShipmentLegService
{
    public function __construct(private ShipmentMilestoneService $milestones)
    {
    }

    public function create(array $data): ShipmentLeg
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = $data['status'] ?? 'planned';
            $data['sequence'] = $data['sequence'] ?? $this->nextSequence($data['shipment_id']);
            $leg = ShipmentLeg::create($data);

            $this->milestones->recordAndComplete([
                'shipment_id' => $leg->shipment_id,
                'shipment_leg_id' => $leg->id,
                'milestone_code' => 'planned',
                'milestone_name' => ShipmentLeg::LIFECYCLE_STAGES['planned'],
            ]);

            return $leg;
        });
    }

    public function update(ShipmentLeg $leg, array $data): ShipmentLeg
    {
        $leg->update($data);

        return $leg;
    }

    public function delete(ShipmentLeg $leg): void
    {
        $leg->delete();
    }

    public function transitionStatus(ShipmentLeg $leg, string $stageCode, array $milestoneData = []): ShipmentLeg
    {
        return DB::transaction(function () use ($leg, $stageCode, $milestoneData) {
            $leg->status = $stageCode;

            if ($stageCode === 'in_progress' && !$leg->actual_departure) {
                $leg->actual_departure = $milestoneData['actual_at'] ?? now();
            }
            if ($stageCode === 'completed' && !$leg->actual_arrival) {
                $leg->actual_arrival = $milestoneData['actual_at'] ?? now();
            }

            $leg->save();

            $this->milestones->recordAndComplete(array_merge([
                'shipment_id' => $leg->shipment_id,
                'shipment_leg_id' => $leg->id,
                'milestone_code' => $stageCode,
                'milestone_name' => ShipmentLeg::LIFECYCLE_STAGES[$stageCode]
                    ?? ucwords(str_replace('_', ' ', $stageCode)),
            ], $milestoneData));

            return $leg;
        });
    }

    /**
     * Creates a minimal, real Trip for own-fleet dispatch of a leg.
     * freight/rate/transporter_freight are deliberately left at 0 - this
     * Trip is purely an operational execution record. All freight
     * revenue/cost continues to flow through FreightCharge/FreightCost
     * (see FreightAccountingService); this must never become a second
     * billing path.
     */
    public function dispatchViaOwnFleet(ShipmentLeg $leg, array $tripData): Trip
    {
        return DB::transaction(function () use ($leg, $tripData) {
            $trip = new Trip();
            $trip->trip_number = $this->generateTripNumber();
            $trip->company_id = $tripData['company_id'] ?? null;
            $trip->customer_id = $tripData['customer_id'] ?? null;
            $trip->transporter_id = $tripData['transporter_id'];
            $trip->horse_id = $tripData['horse_id'] ?? null;
            $trip->vehicle_id = $tripData['vehicle_id'] ?? null;
            $trip->driver_id = $tripData['driver_id'] ?? null;
            $trip->from = $tripData['from'] ?? null;
            $trip->to = $tripData['to'] ?? null;
            $trip->cargo_details = $tripData['cargo_details'] ?? null;
            $trip->start_date = $tripData['start_date'] ?? null;
            $trip->trip_status = $tripData['trip_status'] ?? 'Scheduled';
            $trip->trip_status_date = $tripData['start_date'] ?? now();
            $trip->currency_id = $tripData['currency_id'] ?? null;
            $trip->freight = 0;
            $trip->rate = 0;
            $trip->transporter_freight = 0;
            $trip->save();

            $leg->trip_id = $trip->id;
            $leg->save();

            return $trip;
        });
    }

    /**
     * One-way PULL sync: reads Trip.trip_status and maps it onto this
     * leg's own status/actual timestamps. Never writes to Trip. Idempotent
     * - safe to call on every render.
     */
    public function syncFromTrip(ShipmentLeg $leg): ShipmentLeg
    {
        if (!$leg->trip_id || !$leg->trip) {
            return $leg;
        }

        $map = [
            'Scheduled' => 'planned',
            'Started' => 'planned',
            'Loading Point' => 'in_progress',
            'Loaded' => 'in_progress',
            'InTransit' => 'in_progress',
            'Offloading Point' => 'in_progress',
            'Offloaded' => 'completed',
            'OnHold' => 'on_hold',
            'Cancelled' => 'cancelled',
        ];

        $mapped = $map[$leg->trip->trip_status] ?? null;
        if (!$mapped || $mapped === $leg->status) {
            return $leg;
        }

        $milestoneData = [];
        if ($mapped === 'completed') {
            $milestoneData['actual_at'] = $leg->trip->end_date ? Carbon::parse($leg->trip->end_date) : now();
        }

        return $this->transitionStatus($leg, $mapped, $milestoneData);
    }

    private function nextSequence(int $shipmentId): int
    {
        return (ShipmentLeg::where('shipment_id', $shipmentId)->max('sequence') ?? 0) + 1;
    }

    private function generateTripNumber(): string
    {
        $company = Auth::user()->employee->company ?? null;
        $initials = 'FF';

        if ($company) {
            $words = explode(' ', $company->name);
            $initials = isset($words[1][0]) ? $words[0][0] . $words[1][0] : $words[0][0];
        }

        $lastId = Trip::orderBy('id', 'desc')->value('id') ?? 0;

        return $initials . 'T' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }
}
