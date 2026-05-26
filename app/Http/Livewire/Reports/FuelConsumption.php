<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use App\Models\Fuel;
use App\Models\Horse;
use App\Models\Vehicle;
use Carbon\Carbon;

class FuelConsumption extends Component
{
    public $from;
    public $to;

    public $asset_type = 'all'; // all, horse, vehicle
    public $selectedHorse;
    public $selectedVehicle;

    public $horses = [];
    public $vehicles = [];

    public function mount()
    {
        $this->from = now()->startOfMonth()->format('Y-m-d');
        $this->to = now()->format('Y-m-d');

        $this->horses = Horse::query()
            ->orderBy('registration_number', 'asc')
            ->get();

        $this->vehicles = Vehicle::query()
            ->orderBy('registration_number', 'asc')
            ->get();
    }

    public function updatedAssetType()
    {
        if ($this->asset_type === 'horse') {
            $this->selectedVehicle = null;
        }

        if ($this->asset_type === 'vehicle') {
            $this->selectedHorse = null;
        }
    }

    public function clearFilters()
    {
        $this->from = now()->startOfMonth()->format('Y-m-d');
        $this->to = now()->format('Y-m-d');
        $this->asset_type = 'all';
        $this->selectedHorse = null;
        $this->selectedVehicle = null;
    }

    public function getFuelConsumptionsProperty()
    {
        $fuels = Fuel::query()
            ->with([
                'horse:id,registration_number',
                'vehicle:id,registration_number',
                'driver.employee:id,name,surname',
            ])
            ->when($this->from && $this->to, function ($query) {
                $query->whereBetween('date', [
                    Carbon::parse($this->from)->startOfDay(),
                    Carbon::parse($this->to)->endOfDay(),
                ]);
            })
            ->when($this->asset_type === 'horse', function ($query) {
                $query->whereNotNull('horse_id');
            })
            ->when($this->asset_type === 'vehicle', function ($query) {
                $query->whereNotNull('vehicle_id');
            })
            ->when($this->selectedHorse, function ($query) {
                $query->where('horse_id', $this->selectedHorse);
            })
            ->when($this->selectedVehicle, function ($query) {
                $query->where('vehicle_id', $this->selectedVehicle);
            })
            ->where(function ($query) {
                $query->whereNotNull('horse_id')
                    ->orWhereNotNull('vehicle_id');
            })
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $grouped = $fuels->groupBy(function ($fuel) {
            if ($fuel->horse_id) {
                return 'horse_' . $fuel->horse_id;
            }

            return 'vehicle_' . $fuel->vehicle_id;
        });

        $rows = collect();

        foreach ($grouped as $assetKey => $assetFuels) {
            $assetFuels = $assetFuels->sortBy([
                ['date', 'asc'],
                ['id', 'asc'],
            ])->values();

            $previousFullTank = null;
            $cycleFuel = 0;
            $cycleEntries = [];

            foreach ($assetFuels as $fuel) {
                $isFullTank = (bool) $fuel->is_full_tank;

                if (!$previousFullTank) {
                    if ($isFullTank) {
                        $previousFullTank = $fuel;
                        $cycleFuel = 0;
                        $cycleEntries = [];
                    }

                    continue;
                }

                $cycleFuel += (float) $fuel->quantity;
                $cycleEntries[] = $fuel;

                if ($isFullTank) {
                    $startOdometer = $this->getOdometer($previousFullTank);
                    $endOdometer = $this->getOdometer($fuel);

                    $distance = null;
                    $kmPerLitre = null;
                    $litresPer100Km = null;

                    if (
                        is_numeric($startOdometer) &&
                        is_numeric($endOdometer) &&
                        (float) $endOdometer > (float) $startOdometer &&
                        $cycleFuel > 0
                    ) {
                        $distance = (float) $endOdometer - (float) $startOdometer;
                        $kmPerLitre = $distance / $cycleFuel;
                        $litresPer100Km = ($cycleFuel * 100) / $distance;
                    }

                    $rows->push([
                        'asset_type' => $fuel->horse_id ? 'Horse' : 'Vehicle',
                        'asset_name' => $fuel->horse_id
                            ? optional($fuel->horse)->registration_number
                            : optional($fuel->vehicle)->registration_number,

                        'start_date' => $previousFullTank->date,
                        'end_date' => $fuel->date,

                        'start_odometer' => $startOdometer,
                        'end_odometer' => $endOdometer,
                        'distance' => $distance,

                        'fuel_used' => $cycleFuel,
                        'km_per_litre' => $kmPerLitre,
                        'litres_per_100km' => $litresPer100Km,

                        'entries_count' => count($cycleEntries),
                        'status' => $distance ? 'Closed' : 'Invalid Odometer',
                    ]);

                    $previousFullTank = $fuel;
                    $cycleFuel = 0;
                    $cycleEntries = [];
                }
            }
        }

        return $rows->sortByDesc('end_date')->values();
    }

    public function getSummaryProperty()
    {
        $rows = $this->fuelConsumptions;

        $totalDistance = $rows->sum(function ($row) {
            return is_numeric($row['distance']) ? $row['distance'] : 0;
        });

        $totalFuel = $rows->sum(function ($row) {
            return is_numeric($row['fuel_used']) ? $row['fuel_used'] : 0;
        });

        return [
            'cycles' => $rows->count(),
            'total_distance' => $totalDistance,
            'total_fuel' => $totalFuel,
            'average_km_per_litre' => $totalFuel > 0 ? $totalDistance / $totalFuel : 0,
            'average_litres_per_100km' => $totalDistance > 0 ? ($totalFuel * 100) / $totalDistance : 0,
        ];
    }

    private function getOdometer($fuel)
    {
        // Change this if your column is called odometer_reading instead.
        return $fuel->odometer ?? $fuel->odometer_reading ?? null;
    }

    public function render()
    {
        return view('livewire.reports.fuel-consumption', [
            'fuelConsumptions' => $this->fuelConsumptions,
            'summary' => $this->summary,
        ]);
    }
}