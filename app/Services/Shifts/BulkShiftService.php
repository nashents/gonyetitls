<?php

namespace App\Services\Shifts;

use App\Models\Cargo;
use App\Models\DeliveryNote;
use App\Models\Fuel;
use App\Models\Rehandling;
use App\Models\Shift;
use App\Models\Trip;
use App\Models\TripType;
use App\Services\Fuel\FuelApprovalService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Bulk create shifts (Shifts > Bulk Shifts): one shift per truck/vehicle,
 * saved the way Shifts\Index::store() saves one - the shift and its
 * loading/offloading points, its trips (Offloaded + approved, each with a
 * delivery note and the truck's trailers) or rehandling works, the optional
 * shift fuel order, and the fuel consumption figures. All save or none do.
 *
 * Keep in step with Shifts\Index::store() / createTrips() / createRehandlingWork().
 */
class BulkShiftService
{
    /**
     * @param array $shifts one each: for, type, date, shift_start_time, shift_end_time, team_id, customer_id,
     *                      currency_id, exchange_rate, equipment, horse_id, vehicle_id, transporter_id, driver_id,
     *                      trailer_ids, open_mileage, close_mileage, calculated_mileage, comments,
     *                      depart_workshop_time, arrive_location_time, depart_location_time, arrive_workshop_time,
     *                      trips[] (trip_ref, haulage_type, cargo_id, weight, quantity, measurement, litreage,
     *                      litreage_at_20, rate, from, to, loading_point_id, offloading_point_id, arrive_lp, depart_lp,
     *                      arrive_op, depart_op, starting_mileage, ending_mileage, starting_hours, ending_hours),
     *                      works[] (work_id, location_id, cargo_id, start_time, starting_hours, starting_mileage,
     *                      stop_time, ending_hours, ending_mileage, weight, loads),
     *                      fuel (null, or: source, container_id, source_horse_id, category, account_id, fuel_type,
     *                      is_full_tank, allocation_to, currency_id, exchange_rate, quantity, unit_price, amount,
     *                      mileage, hours, comments)
     * @return Collection<int, Shift>
     */
    public function create(array $shifts, $company, $user): Collection
    {
        $tripType = TripType::where('name', 'Local')->first();
        $cargoTypes = Cargo::pluck('type', 'id');

        return DB::transaction(function () use ($shifts, $company, $user, $tripType, $cargoTypes) {
            $created = collect();

            foreach ($shifts as $data) {
                $created->push($this->createShift($data, $company, $user, $tripType, $cargoTypes));
            }

            return $created;
        });
    }

    private function createShift(array $d, $company, $user, $tripType, $cargoTypes): Shift
    {
        $isTrips = $d['for'] === 'Trips';
        $fuel = $d['fuel'] ?? null;
        $horseId = $d['equipment'] === 'Horse' ? ($d['horse_id'] ?: null) : null;
        $vehicleId = $d['equipment'] === 'Vehicle' ? ($d['vehicle_id'] ?: null) : null;

        $shift = new Shift;
        $shift->user_id = $user->id;
        $shift->shift_number = $this->number($company, Shift::class, 'ST');
        $shift->for = $d['for'];
        $shift->type = $d['type'];
        $shift->shift_start_time = $d['shift_start_time'];
        $shift->shift_end_time = $d['shift_end_time'];
        $shift->customer_id = $d['customer_id'] ?: null;
        $shift->team_id = $d['team_id'] ?: null;
        $shift->driver_id = $d['driver_id'] ?: null;
        $shift->currency_id = $d['currency_id'] ?: null;
        $shift->transporter_id = $d['transporter_id'] ?: null;
        $shift->horse_id = $horseId;
        $shift->vehicle_id = $vehicleId;
        $shift->equipment = $d['equipment'];
        $shift->fuel_order = (bool) $fuel;
        $shift->total_fuel = $fuel ? $fuel['quantity'] : 0;
        $shift->open_mileage = $this->num($d['open_mileage'] ?? null);
        $shift->close_mileage = $this->num($d['close_mileage'] ?? null);
        $shift->comments = $d['comments'] ?: null;

        if (! $isTrips) {
            $shift->depart_workshop_time = $d['depart_workshop_time'] ?: null;
            $shift->arrive_location_time = $d['arrive_location_time'] ?: null;
            $shift->depart_location_time = $d['depart_location_time'] ?: null;
            $shift->arrive_workshop_time = $d['arrive_workshop_time'] ?: null;
        }

        if ($isTrips && is_numeric($shift->open_mileage) && is_numeric($shift->close_mileage)) {
            $shift->actual_mileage = $shift->close_mileage - $shift->open_mileage;
            if ($shift->actual_mileage > 0 && $fuel && $fuel['quantity'] > 0) {
                $shift->fuel_consumption_mileage = $shift->actual_mileage / $fuel['quantity'];
            }
        }

        $shift->calculated_mileage = $isTrips ? $this->num($d['calculated_mileage'] ?? null) : null;
        $shift->date = $d['date'];
        $shift->exchange_rate = $d['exchange_rate'] ?: null;
        $shift->status = '1';
        $shift->save();

        if ($isTrips) {
            $trips = $d['trips'] ?? [];
            $shift->loading_points()->sync(array_values(array_unique(array_filter(array_column($trips, 'loading_point_id')))));
            $shift->offloading_points()->sync(array_values(array_unique(array_filter(array_column($trips, 'offloading_point_id')))));

            foreach ($trips as $trip) {
                $this->createTrip($trip, $shift, $d, $company, $user, $tripType, $cargoTypes);
            }
        } else {
            foreach ($d['works'] ?? [] as $work) {
                $this->createWork($work, $shift, $d, $company, $user);
            }
        }

        if ($fuel) {
            $this->createFuel($fuel, $shift, $d, $company, $user, $horseId, $vehicleId);
            if (! $isTrips) {
                $this->rehandlingFuelConsumption($shift);
            }
        }

        return $shift;
    }

    private function createTrip(array $t, Shift $shift, array $d, $company, $user, $tripType, $cargoTypes): Trip
    {
        $cargoId = filled($t['cargo_id'] ?? null) ? $t['cargo_id'] : null;
        $cargoType = $cargoId ? ($cargoTypes[$cargoId] ?? null) : null;

        $trip = new Trip;
        $trip->trip_number = $this->number($company, Trip::class, 'T');
        $trip->trip_ref = filled($t['trip_ref'] ?? null) ? trim($t['trip_ref']) : null;
        $trip->shift_id = $shift->id;
        $trip->user_id = $user->id;
        $trip->company_id = $company->id ?? null;
        $trip->horse_id = $shift->horse_id;
        $trip->vehicle_id = $shift->vehicle_id;
        $trip->transporter_id = $shift->transporter_id;
        $trip->driver_id = $shift->driver_id;
        $trip->customer_id = $shift->customer_id;
        $trip->freight_calculation = 'rate_weight';
        $trip->calculation_measurement = 'weight';
        $trip->currency_id = $shift->currency_id ?: ($company->currency_id ?? null);
        $trip->exchange_rate = $shift->exchange_rate;
        $trip->start_date = $d['date'];
        $trip->end_date = $d['date'];
        $trip->trip_type_id = $tripType?->id;
        $trip->with_cargos = true;

        if (filled($t['arrive_lp'] ?? null) && filled($t['depart_lp'] ?? null)) {
            $trip->arrive_loading_point = $t['arrive_lp'];
            $trip->depart_loading_point = $t['depart_lp'];
            $trip->loading_time = $this->timeDifference($t['arrive_lp'], $t['depart_lp']);
        }
        if (filled($t['arrive_op'] ?? null) && filled($t['depart_op'] ?? null)) {
            $trip->arrive_offloading_point = $t['arrive_op'];
            $trip->depart_offloading_point = $t['depart_op'];
            $trip->offloading_time = $this->timeDifference($t['arrive_op'], $t['depart_op']);
        }

        $trip->cargo_id = $cargoId;
        $trip->haulage_type = $t['haulage_type'] ?: null;
        $trip->starting_mileage = $this->num($t['starting_mileage'] ?? null);
        $trip->ending_mileage = $this->num($t['ending_mileage'] ?? null);
        $trip->starting_hours = $this->num($t['starting_hours'] ?? null);
        $trip->ending_hours = $this->num($t['ending_hours'] ?? null);
        if (is_numeric($trip->starting_mileage) && is_numeric($trip->ending_mileage) && $trip->ending_mileage >= $trip->starting_mileage) {
            $trip->distance = $trip->ending_mileage - $trip->starting_mileage;
        }
        if (is_numeric($trip->starting_hours) && is_numeric($trip->ending_hours) && $trip->ending_hours >= $trip->starting_hours) {
            $trip->hours_distance = $trip->ending_hours - $trip->starting_hours;
        }

        $trip->from = $t['from'] ?: null;
        $trip->to = $t['to'] ?: null;
        $trip->loading_point_id = $t['loading_point_id'] ?: null;
        $trip->offloading_point_id = $t['offloading_point_id'] ?: null;
        $trip->rate = $this->num($t['rate'] ?? null);
        $trip->weight = $this->num($t['weight'] ?? null);
        $trip->litreage = $this->num($t['litreage'] ?? null);
        $trip->litreage_at_20 = $this->num($t['litreage_at_20'] ?? null);
        $trip->quantity = $this->num($t['quantity'] ?? null);
        $trip->measurement = $t['measurement'] ?: null;

        $freight = static::freight($t, $cargoType);
        if ($freight !== null) {
            $trip->freight = $freight;
            $trip->turnover = $freight;
            if ($trip->currency_id != ($company->currency_id ?? null) && is_numeric($trip->exchange_rate) && $trip->exchange_rate > 0) {
                $trip->exchange_customer_freight = $freight * $trip->exchange_rate;
            }
        }

        $trip->trip_status = 'Offloaded';
        $trip->trip_status_date = $d['date'];
        $trip->authorization = 'approved';
        $trip->authorization_date = date('Y-m-d');
        $trip->authorized_by_id = $user->id;
        $trip->save();

        if (! empty($d['trailer_ids'])) {
            $trip->trailers()->sync($d['trailer_ids']);
        }

        $note = new DeliveryNote;
        $note->user_id = $user->id;
        $note->trip_id = $trip->id;
        $note->measurement = $trip->measurement;
        $note->distance = $trip->distance;
        $note->loaded_date = $trip->start_date;
        $note->offloaded_date = $trip->end_date;
        if ($cargoType === 'Liquid') {
            $note->loaded_litreage = $trip->litreage;
            $note->loaded_litreage_at_20 = $trip->litreage_at_20;
        } elseif ($cargoType === 'Solid') {
            $note->loaded_quantity = $trip->quantity;
        }
        $note->loaded_weight = $trip->weight;
        $note->loaded_rate = $trip->rate;
        $note->loaded_freight = $trip->freight;
        $note->save();

        return $trip;
    }

    /** Freight = rate x weight (solid) or rate x litreage @ 20 (liquid). */
    public static function freight(array $t, ?string $cargoType): ?float
    {
        $measure = $cargoType === 'Liquid' ? ($t['litreage_at_20'] ?? null) : ($t['weight'] ?? null);

        return is_numeric($t['rate'] ?? null) && is_numeric($measure) ? round($t['rate'] * $measure, 2) : null;
    }

    private function createWork(array $w, Shift $shift, array $d, $company, $user): Rehandling
    {
        $rehandling = new Rehandling;
        $rehandling->user_id = $user->id;
        $rehandling->shift_id = $shift->id;
        $rehandling->rehandling_number = $this->number($company, Rehandling::class, 'R');
        $rehandling->currency_id = $shift->currency_id;
        $rehandling->work_id = $w['work_id'] ?: null;
        $rehandling->location_id = $w['location_id'] ?: null;
        $rehandling->cargo_id = filled($w['cargo_id'] ?? null) ? $w['cargo_id'] : null;
        $rehandling->start_time = $w['start_time'] ?: null;
        $rehandling->open_hours = $this->num($w['starting_hours'] ?? null);
        $rehandling->open_mileage = $this->num($w['starting_mileage'] ?? null);
        $rehandling->stop_time = $w['stop_time'] ?: null;
        $rehandling->close_hours = $this->num($w['ending_hours'] ?? null);
        $rehandling->close_mileage = $this->num($w['ending_mileage'] ?? null);
        $rehandling->weight = $this->num($w['weight'] ?? null);
        $rehandling->loads = $this->num($w['loads'] ?? null);
        $rehandling->save();

        return $rehandling;
    }

    private function createFuel(array $f, Shift $shift, array $d, $company, $user, $horseId, $vehicleId): Fuel
    {
        $fromTruck = $horseId && $f['source'] === 'truck' && filled($f['source_horse_id'] ?? null);
        $foreign = $f['currency_id'] && $f['currency_id'] != ($company->currency_id ?? null);

        $fuel = new Fuel;
        $fuel->user_id = $user->id;
        $fuel->order_number = $this->number($company, Fuel::class, 'FO');
        $fuel->horse_id = $horseId;
        $fuel->vehicle_id = $vehicleId;
        $fuel->account_id = $f['account_id'] ?: null;
        $fuel->currency_id = $f['currency_id'] ?: null;
        $fuel->shift_id = $shift->id;
        $fuel->type = $vehicleId ? 'Vehicle' : ($horseId ? 'Horse' : null);
        $fuel->driver_id = $shift->driver_id;
        $fuel->source_horse_id = $fromTruck ? $f['source_horse_id'] : null;
        $fuel->container_id = $fromTruck ? null : ($f['container_id'] ?: null);
        $fuel->fuel_type = $f['fuel_type'];
        $fuel->is_full_tank = $f['is_full_tank'];
        $fuel->deduct_from = $f['deduct_from'] ?? 'quantity';
        $fuel->date = $d['date'];
        $fuel->unit_price = $this->num($f['unit_price'] ?? null) ?? 0;
        $fuel->quantity = $f['quantity'];
        $fuel->amount = $this->num($f['amount'] ?? null);
        $fuel->odometer = $this->num($f['mileage'] ?? null);
        $fuel->hours = $this->num($f['hours'] ?? null);
        $fuel->category = $f['category'];
        $fuel->exchange_rate = $foreign ? $this->num($f['exchange_rate'] ?? null) : null;
        $fuel->exchange_amount = $foreign && is_numeric($fuel->exchange_rate) && is_numeric($fuel->amount) ? $fuel->exchange_rate * $fuel->amount : null;
        $fuel->fillup = 1;
        $fuel->status = 1;
        $fuel->comments = $f['comments'] ?: null;
        $fuel->allocation_to = $f['allocation_to'] ?: null;
        $fuel->save();

        // Approved on creation, like the shift's trips
        app(FuelApprovalService::class)->approve($fuel, $user->id, 'Auto-approved with shift ' . $shift->shift_number);

        return $fuel;
    }

    /** As Shifts\Index::calculateFuelConsumption(): rehandling km/L and hours/L from first to last work. */
    private function rehandlingFuelConsumption(Shift $shift): void
    {
        $shift->load('fuel', 'rehandlings');
        $fuel = $shift->fuel;
        $works = $shift->rehandlings;
        if (! $fuel || ! is_numeric($fuel->quantity) || $fuel->quantity <= 0 || $works->isEmpty()) {
            return;
        }

        $first = $works->first();
        $last = $works->sortByDesc('id')->first();

        if (is_numeric($first->open_mileage) && is_numeric($last->close_mileage) && $last->close_mileage - $first->open_mileage > 0) {
            $shift->fuel_consumption_mileage = ($last->close_mileage - $first->open_mileage) / $fuel->quantity;
        }
        if (is_numeric($first->open_hours) && is_numeric($last->close_hours) && $last->close_hours - $first->open_hours > 0) {
            $shift->fuel_consumption_hours = ($last->close_hours - $first->open_hours) / $fuel->quantity;
        }
        $shift->save();
    }

    /** Company initials + prefix + next id, as Shifts\Index::shiftNumber()/tripNumber()/orderNumber()/rehandlingNumber(). */
    private function number($company, string $model, string $prefix): string
    {
        $words = explode(' ', (string) ($company->name ?? ''));
        $initials = ($words[0][0] ?? '') . ($words[1][0] ?? '');

        return $initials . $prefix . str_pad(((int) $model::max('id')) + 1, 5, '0', STR_PAD_LEFT);
    }

    private function timeDifference($start, $end): ?string
    {
        $diff = Carbon::parse($end)->diffInMinutes(Carbon::parse($start));

        return sprintf('%02d:%02d', floor($diff / 60), $diff % 60);
    }

    private function num($value)
    {
        return is_numeric($value) ? $value : null;
    }
}
