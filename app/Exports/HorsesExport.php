<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Horse;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class HorsesExport implements
    FromQuery,
    ShouldAutoSize,
    WithMapping,
    WithHeadings,
    WithEvents,
    WithDrawings,
    WithCustomStartCell
{
    use Exportable;

    public $from;
    public $to;
    public $filters;
    public $search;
    public $totals;

    public function __construct($from = null, $to = null, $filters = [], $search = null)
    {
        $this->from = $from ? Carbon::parse($from)->startOfDay() : now()->startOfYear()->startOfDay();
        $this->to   = $to ? Carbon::parse($to)->endOfDay() : now()->endOfYear()->endOfDay();
        $this->filters = $filters ?? [];
        $this->search = $search;

        $this->buildSummary();
    }

    protected function buildSummary(): void
    {
        $availableTrucks = Horse::query()
            ->where('archive', 0)
            ->count();

        $tripRows = Trip::query()
            ->whereNotNull('horse_id')
            ->whereBetween('start_date', [$this->from, $this->to])
            ->get([
                'id',
                'horse_id',
                'starting_mileage',
                'ending_mileage',
                'distance',
                'weight',
                'litreage_at_20',
            ]);

        $activeTruckIds = $tripRows
            ->pluck('horse_id')
            ->filter()
            ->unique()
            ->values();

        $activeTrucks = $activeTruckIds->count();
        $totalTrips   = $tripRows->count();

        $totalDistance = (float) $tripRows->sum(function ($trip) {
            $startMileage = is_numeric($trip->starting_mileage) ? (float) $trip->starting_mileage : null;
            $endMileage   = is_numeric($trip->ending_mileage) ? (float) $trip->ending_mileage : null;
            $distance     = is_numeric($trip->distance) ? (float) $trip->distance : 0;

            if (!is_null($startMileage) && !is_null($endMileage) && $endMileage >= $startMileage) {
                return $endMileage - $startMileage;
            }

            return $distance;
        });

        $totalWeight = (float) $tripRows->sum(function ($trip) {
            return is_numeric($trip->weight) ? (float) $trip->weight : 0;
        });

        $totalVolume = (float) $tripRows->sum(function ($trip) {
            return is_numeric($trip->litreage_at_20) ? (float) $trip->litreage_at_20 : 0;
        });

        $avgTripsPerTruck = $activeTrucks > 0 ? round($totalTrips / $activeTrucks, 2) : 0;
        $avgDistancePerTruck = $activeTrucks > 0 ? round($totalDistance / $activeTrucks, 2) : 0;
        $avgWeightPerTruck = $activeTrucks > 0 ? round($totalWeight / $activeTrucks, 2) : 0;
        $avgVolumePerTruck = $activeTrucks > 0 ? round($totalVolume / $activeTrucks, 2) : 0;
        $utilisationRatio = $availableTrucks > 0 ? round(($activeTrucks / $availableTrucks) * 100, 2) : 0;

        $this->totals = (object) [
            'from' => $this->from->format('d M Y'),
            'to' => $this->to->format('d M Y'),
            'available_trucks' => $availableTrucks,
            'active_trucks' => $activeTrucks,
            'total_trips' => $totalTrips,
            'total_distance' => $totalDistance,
            'total_weight' => $totalWeight,
            'total_volume' => $totalVolume,
            'avg_trips_per_truck' => $avgTripsPerTruck,
            'avg_distance_per_truck' => $avgDistancePerTruck,
            'avg_weight_per_truck' => $avgWeightPerTruck,
            'avg_volume_per_truck' => $avgVolumePerTruck,
            'utilisation_ratio' => $utilisationRatio,
        ];
    }

    public function query()
    {
        $query = Horse::query()
            ->with([
                'horse_make:id,name',
                'horse_model:id,name',
                'transporter:id,name',
                'horse_type:id,name',
                'horse_group:id,name',
            ])
            ->where('archive', 0);

        if (!empty($this->filters['filter_transporter_id'])) {
            $query->where('transporter_id', $this->filters['filter_transporter_id']);
        }

        if (!empty($this->filters['filter_horse_type_id'])) {
            $query->where('horse_type_id', $this->filters['filter_horse_type_id']);
        }

        if (!empty($this->filters['filter_horse_group_id'])) {
            $query->where('horse_group_id', $this->filters['filter_horse_group_id']);
        }

        if (!empty($this->filters['filter_status'])) {
            if ($this->filters['filter_status'] === 'available') {
                $query->where('status', 1);
            } elseif ($this->filters['filter_status'] === 'unavailable') {
                $query->where('status', '!=', 1);
            }
        }

        $query->when(filled($this->search), function (Builder $q) {
            $term = trim($this->search);
            $like = "%{$term}%";

            $q->where(function (Builder $qq) use ($like) {
                $qq->where('horse_number', 'like', $like)
                    ->orWhere('registration_number', 'like', $like)
                    ->orWhere('fleet_number', 'like', $like)
                    ->orWhere('manufacturer', 'like', $like)
                    ->orWhere('color', 'like', $like)
                    ->orWhere('chasis_number', 'like', $like)
                    ->orWhere('engine_number', 'like', $like)
                    ->orWhere('country_of_origin', 'like', $like)
                    ->orWhereHas('horse_make', fn (Builder $x) => $x->where('name', 'like', $like))
                    ->orWhereHas('horse_model', fn (Builder $x) => $x->where('name', 'like', $like))
                    ->orWhereHas('transporter', fn (Builder $x) => $x->where('name', 'like', $like))
                    ->orWhereHas('horse_type', fn (Builder $x) => $x->where('name', 'like', $like))
                    ->orWhereHas('horse_group', fn (Builder $x) => $x->where('name', 'like', $like));
            });
        });

        return $query->orderBy('registration_number', 'asc');
    }

    public function map($horse): array
    {
        $status = (int) $horse->status === 1 ? 'Available' : 'Unavailable';

        $fleetNumber = $horse->fleet_number ? '(' . $horse->fleet_number . ')' : '';

        $make = $horse->horse_make ? $horse->horse_make->name : '';
        $model = $horse->horse_model ? $horse->horse_model->name : '';
        $makeModel = trim($make . ' ' . $model);

        $age = '';
        if (!empty($horse->year) && is_numeric($horse->year)) {
            $age = now()->year - (int) $horse->year;
        }

        return [
            $horse->horse_number,
            trim(($horse->registration_number ?? '') . ' ' . $fleetNumber),
            $makeModel,
            optional($horse->transporter)->name ?? '',
            optional($horse->horse_type)->name ?? '',
            optional($horse->horse_group)->name ?? '',
            $horse->year,
            $age !== '' ? $age . ' Year(s)' : '',
            $horse->color,
            $horse->gvm,
            $horse->nvm,
            $horse->condition,
            $horse->manufacturer,
            $horse->country_of_origin,
            $horse->chasis_number,
            $horse->engine_number,
            $horse->mileage,
            $horse->fuel_type,
            $horse->fuel_measurement,
            $horse->fuel_consumption,
            $horse->track_usage,
            $horse->no_of_wheels,
            $status,
        ];
    }

    public function headings(): array
    {
        return [
            'Horse#',
            'Reg & Fleet#',
            'Make & Model',
            'Transporter',
            'Horse Type',
            'Horse Group',
            'Year',
            'Age',
            'Color',
            'GVM',
            'NVM',
            'Condition',
            'Manufacturer',
            'Origin',
            'Chasis#',
            'Engine#',
            'Mileage',
            'Fuel Type',
            'Fuel Measurement',
            'Fuel Consumption',
            'Track Usage',
            'No of Wheels',
            'Availability',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Heading style
                $event->sheet->getStyle('A13:W13')->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ],
                ]);

                // Summary block
                $row = 2;
                $initial_row = 2;

                $event->sheet->setCellValue("C{$row}", 'Fleet Summary');
                $event->sheet->mergeCells("C{$row}:D{$row}");
                $event->sheet->getStyle("C{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                    ],
                ]);

                $row++;

                $event->sheet->setCellValue("C{$row}", 'Reporting Period');
                $event->sheet->setCellValue("D{$row}", ($this->totals->from ?? '') . ' - ' . ($this->totals->to ?? ''));

                $row++;
                $event->sheet->setCellValue("C{$row}", 'Available Trucks');
                $event->sheet->setCellValue("D{$row}", $this->totals->available_trucks ?? 0);

                $row++;
                $event->sheet->setCellValue("C{$row}", 'Active Trucks');
                $event->sheet->setCellValue("D{$row}", $this->totals->active_trucks ?? 0);

                $row++;
                $event->sheet->setCellValue("C{$row}", 'Distance Travelled (Km)');
                $event->sheet->setCellValue("D{$row}", $this->totals->total_distance ?? 0);

                $row++;
                $event->sheet->setCellValue("C{$row}", 'Avg No. of Trips per Truck');
                $event->sheet->setCellValue("D{$row}", $this->totals->avg_trips_per_truck ?? 0);

                $row++;
                $event->sheet->setCellValue("C{$row}", 'Avg Distance per Truck (Km)');
                $event->sheet->setCellValue("D{$row}", $this->totals->avg_distance_per_truck ?? 0);

                $row++;
                $event->sheet->setCellValue("C{$row}", 'Avg Weight per Truck');
                $event->sheet->setCellValue("D{$row}", $this->totals->avg_weight_per_truck ?? 0);

                $row++;
                $event->sheet->setCellValue("C{$row}", 'Avg Volume per Truck');
                $event->sheet->setCellValue("D{$row}", $this->totals->avg_volume_per_truck ?? 0);

                $row++;
                $event->sheet->setCellValue("C{$row}", 'Utilisation Ratio');
                $event->sheet->setCellValue("D{$row}", ($this->totals->utilisation_ratio ?? 0) . '%');
                
                $row++;

                $final_row = $row-1;
                // Summary style
                $event->sheet->getStyle("C{$initial_row}:D{$final_row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FCE4D6'],
                    ],
                ]);

                // Optional borders for summary
                $event->sheet->getStyle("C{$initial_row}:D{$final_row}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D9D9D9'],
                        ],
                    ],
                ]);
            },
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();

        if (isset(Auth::user()->employee->company)) {
            $drawing->setName(Auth::user()->employee->company->name);
            $drawing->setDescription(Auth::user()->employee->company->name . ' Logo');

            if (
                !empty(Auth::user()->employee->company->logo) &&
                file_exists(public_path('/images/uploads/' . Auth::user()->employee->company->logo))
            ) {
                $drawing->setPath(public_path('/images/uploads/' . Auth::user()->employee->company->logo));
            } else {
                $drawing->setPath(public_path('/images/uploads/logo.png'));
            }
        } else {
            $drawing->setPath(public_path('/images/uploads/logo.png'));
        }

        $drawing->setHeight(90);
        $drawing->setCoordinates('A2');

        return $drawing;
    }

    public function startCell(): string
    {
        return 'A13';
    }
}