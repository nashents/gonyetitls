<?php

namespace App\Services\Fuel;

use App\Mail\FuelOrderMail;
use App\Models\Container;
use App\Models\Fuel;
use App\Models\Horse;
use App\Models\Vehicle;
use App\Services\Accounting\FuelJournalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Auto-approves a fuel order and applies the same side-effects as approving it
 * on Fuels > Pending (Fuels\Pending::update()): horse/vehicle fuel balance +
 * mileage, source-truck balance, Bulk Buy station stock, the consumption
 * journal (Bill) and, optionally, the order email to the station. Sage
 * "PR - Diesel" sync fires on its own via FuelObserver when authorization
 * flips to approved.
 *
 * Used for fuel orders raised from shifts, which are approved on creation
 * like the shift's trips. Callers own the DB transaction.
 */
class FuelApprovalService
{
    public function approve(Fuel $fuel, ?int $authorizedById = null, ?string $reason = null, bool $emailStation = true): void
    {
        if ($fuel->authorization === 'approved') {
            return;
        }

        $fuel->authorized_by_id = $authorizedById ?? Auth::id();
        $fuel->authorization = 'approved';
        $fuel->authorization_date = now();
        $fuel->reason = $reason;
        $fuel->save();

        $container = $fuel->container_id ? Container::find($fuel->container_id) : null;

        // As Pending::update(): without a station or source truck nothing is
        // drawn, so only the status changes.
        if (!$container && !$fuel->source_horse_id) {
            return;
        }

        $qty = is_numeric($fuel->quantity) ? (float) $fuel->quantity : null;

        if ($fuel->horse_id && $horse = Horse::find($fuel->horse_id)) {
            if ($qty !== null && is_numeric($horse->fuel_balance)) {
                $horse->fuel_balance = $horse->fuel_balance + $qty;
            }
            if (is_numeric($fuel->odometer) && $fuel->odometer > $horse->mileage) {
                $horse->mileage = $fuel->odometer;
            }
            $horse->save();
        }

        if ($fuel->vehicle_id && $vehicle = Vehicle::find($fuel->vehicle_id)) {
            if ($qty !== null && is_numeric($vehicle->fuel_balance)) {
                $vehicle->fuel_balance = $vehicle->fuel_balance + $qty;
            }
            if (is_numeric($fuel->odometer) && $fuel->odometer > $vehicle->mileage) {
                $vehicle->mileage = $fuel->odometer;
            }
            $vehicle->save();
        }

        if ($fuel->source_horse_id && $qty !== null) {
            $source = Horse::find($fuel->source_horse_id);
            if ($source && is_numeric($source->fuel_balance)) {
                $source->fuel_balance = $source->fuel_balance - $qty;
                $source->save();
            }
        }

        if (!$container) {
            return;
        }

        if ($container->purchase_type === 'Bulk Buy') {
            if ($fuel->deduct_from === 'account') {
                if (is_numeric($container->account_balance) && is_numeric($fuel->amount) && $container->account_balance >= $fuel->amount) {
                    $container->account_balance = $container->account_balance - $fuel->amount;
                }
            } elseif ($qty !== null && is_numeric($container->balance) && $container->balance >= $qty) {
                $container->balance = $container->balance - $qty;
            }
            $container->save();
        }

        // Logged, never blocks the shift (e.g. unseeded Fuel - COGS/Ops accounts).
        app(FuelJournalService::class)->postConsumptionSafely($fuel->fresh());

        if ($emailStation) {
            $this->emailStation($fuel, $container);
        }
    }

    protected function emailStation(Fuel $fuel, Container $container): void
    {
        if (!filter_var($container->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            $user = Auth::user();
            $company = $user?->company ?? $user?->employee?->company;
            $trip = $fuel->trip;
            $creator = $fuel->user?->employee;

            Mail::to($container->email)->send(new FuelOrderMail([
                'station_email' => $container->email,
                'station_name' => $container->name,
                'date' => $fuel->date,
                'order_number' => $fuel->order_number,
                'driver' => $fuel->driver,
                'horse' => $fuel->horse,
                'vehicle' => $fuel->vehicle,
                'regnumber' => $fuel->horse ? $fuel->horse->registration_number : '',
                'authorized_by' => $user?->employee ? $user->employee->name . ' ' . $user->employee->surname : '',
                'checked_by' => $creator ? $creator->name . ' ' . $creator->surname : '',
                'collection_point' => $trip?->loading_point?->name ?? '',
                'delivery_point' => $trip?->offloading_point?->name ?? '',
                'fuel_type' => $container->fuel_type,
                'quantity' => $fuel->quantity,
            ], $company));
        } catch (\Throwable $e) {
            Log::warning("Fuel order {$fuel->order_number}: station email failed: " . $e->getMessage());
        }
    }
}
