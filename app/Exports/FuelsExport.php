<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Fuel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class FuelsExport implements  FromQuery,
ShouldAutoSize,
WithMapping,
WithHeadings,
WithEvents,
WithDrawings,
WithCustomStartCell
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */

    public $from;
    public $to;
    public $fuel_filter;
    public $container_id;
    // Equipment + search filters from the fuel orders table (see applyFilters)
    public $filters = [];
    // Summary blocks shown above the table, and the row the table starts on
    public $summary = [];
    public $headingRow = 7;

    public function __construct($from, $to, $fuel_filter, $container_id, array $filters = [])
    {
            $this->from = $from;
            $this->to = $to;
            $this->fuel_filter = $fuel_filter ?: 'created_at';
            $this->container_id = $container_id;
            $this->filters = $filters;

            $this->buildSummary();
    }

    /**
     * Fuel and cost figures count approved orders only - a pending or
     * rejected order hasn't necessarily been fuelled. Status counts show
     * the rest.
     */
    protected function buildSummary(): void
    {
        $fuels = $this->query()->get();
        $approved = $fuels->filter(fn ($f) => $f->authorization === 'approved');
        $litres = fn ($f) => is_numeric($f->quantity) ? (float) $f->quantity : 0.0;
        $cost = fn ($f) => is_numeric($f->amount) ? (float) $f->amount : 0.0;
        $money = function ($rows) use ($cost) {
            return $rows->groupBy(fn ($f) => optional($f->currency)->name ?: '-')
                ->map(fn ($g, $cur) => $cur . ' ' . number_format($g->sum($cost), 2))
                ->implode('; ');
        };
        $equipmentOf = function ($f) {
            if ($f->type === 'Vehicle') {
                return $f->vehicle;
            }
            return in_array($f->type, ['Horse', 'Trip']) ? ($f->horse ?? optional($f->trip)->horse) : null;
        };

        $period = filled($this->from) && filled($this->to)
            ? Carbon::parse($this->from)->format('d M Y') . ' - ' . Carbon::parse($this->to)->format('d M Y')
            : now()->format('F Y');

        $totalLitres = $approved->sum($litres);

        $overview = [
            ['Period', $period],
            ['Total Orders', $fuels->count()],
            ['Approved / Pending / Rejected',
                $approved->count() . ' / '
                . $fuels->where('authorization', 'pending')->count() . ' / '
                . $fuels->where('authorization', 'rejected')->count()],
            ['Total Fuel (L)', round($totalLitres, 2)],
        ];
        foreach ($approved->groupBy(fn ($f) => optional($f->container)->fuel_type ?: 'Unspecified') as $type => $rows) {
            $overview[] = ["  {$type} (L)", round($rows->sum($litres), 2)];
        }
        $overview[] = ['Initial Fills / Top Ups', $approved->where('fillup', 1)->count() . ' / ' . $approved->where('fillup', '!=', 1)->count()];
        $overview[] = ['Avg Litres per Order', $approved->count() ? round($totalLitres / $approved->count(), 2) : 0];
        $overview[] = ['Equipment Fuelled', $approved->map($equipmentOf)->filter()->unique(fn ($e) => get_class($e) . $e->id)->count()];
        $overview[] = ['Fuel From Trucks (L)', round($approved->whereNotNull('source_horse_id')->sum($litres), 2)];
        foreach ($approved->groupBy(fn ($f) => optional($f->currency)->name ?: '-') as $cur => $rows) {
            $l = $rows->sum($litres);
            $overview[] = ["Total Cost ({$cur})", round($rows->sum($cost), 2)];
            $overview[] = ["  Avg Price / L ({$cur})", $l > 0 ? round($rows->sum($cost) / $l, 3) : 0];
        }

        $sourceOf = fn ($f) => $f->source_horse
            ? 'Truck: ' . $f->source_horse->identifier_label
            : (optional($f->container)->name ?: 'Unspecified');
        $stations = $approved->groupBy($sourceOf)
            ->map(fn ($rows, $name) => [$name, $rows->count(), round($rows->sum($litres), 2), $money($rows)])
            ->sortByDesc(fn ($r) => $r[2])->values()->all();

        $byFor = $approved->groupBy(fn ($f) => $f->type ?: 'Other')
            ->map(fn ($rows, $type) => [$type, $rows->count(), round($rows->sum($litres), 2)])
            ->sortByDesc(fn ($r) => $r[2])->values()->all();

        $topEquipment = $approved->filter($equipmentOf)
            ->groupBy(fn ($f) => $equipmentOf($f)->identifier_label)
            ->map(fn ($rows, $label) => [$label, $rows->count(), round($rows->sum($litres), 2)])
            ->sortByDesc(fn ($r) => $r[2])->take(10)->values()->all();

        $this->summary = compact('overview', 'stations', 'byFor', 'topEquipment');

        // Table starts below the tallest block (title row 7, block headers row 8)
        $tallest = max(count($overview) + 1, count($stations) + 1, count($byFor) + 1, count($topEquipment) + 1);
        $this->headingRow = 8 + $tallest + 2;
    }
    public function query()
    {
        $query = Fuel::query()->with(['container','horse','source_horse',
            'horse.horse_make','horse.horse_model','vehicle.vehicle_make','vehicle.vehicle_model','asset.product','trip.horse.horse_make','trip.horse.horse_model','user','currency']);

        if (filled($this->from) && filled($this->to)) {
            $query->whereBetween($this->fuel_filter, [$this->from, $this->to]);
        } elseif (! filled($this->filters['search'] ?? null)) {
            // Same as the fuel orders table: default to this month, but not
            // while searching so matches outside the month still show
            $query->whereMonth($this->fuel_filter, date('m'))
                ->whereYear($this->fuel_filter, date('Y'));
        }

        if (filled($this->container_id)) {
            $query->where('container_id', $this->container_id);
        }

        static::applyFilters($query, $this->filters);

        return $query->orderBy('created_at','desc');
    }

    /**
     * Equipment + search filters shared by the fuel orders table and this
     * export, so a download always matches what's on screen.
     * Keys: type (Horse|Vehicle), equipment_id, search.
     */
    public static function applyFilters($query, array $filters)
    {
        $type = $filters['type'] ?? null;
        $equipmentId = $filters['equipment_id'] ?? null;

        if ($type === 'Horse') {
            // Trip fuel orders fuel the trip's horse
            $query->whereIn('type', ['Horse', 'Trip']);
            if (filled($equipmentId)) {
                $query->where(function ($q) use ($equipmentId) {
                    $q->where('horse_id', $equipmentId)
                        ->orWhere(function ($q) use ($equipmentId) {
                            $q->whereNull('horse_id')
                                ->whereHas('trip', fn ($t) => $t->where('horse_id', $equipmentId));
                        });
                });
            }
        } elseif ($type === 'Vehicle') {
            $query->where('type', 'Vehicle');
            if (filled($equipmentId)) {
                $query->where('vehicle_id', $equipmentId);
            }
        }

        if (filled($filters['search'] ?? null)) {
            $search = '%' . $filters['search'] . '%';

            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', $search)
                    ->orWhere('quantity', 'like', $search)
                    ->orWhere('comments', 'like', $search)
                    ->orWhereHas('horse', function ($q) use ($search) {
                        $q->where('registration_number', 'like', $search)
                        ->orWhere('fleet_number', 'like', $search);
                    })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where(DB::raw("concat(name, ' ', surname)"), 'like', $search);
                    })
                    ->orWhereHas('vehicle', function ($q) use ($search) {
                        $q->where('registration_number', 'like', $search);
                    })
                    ->orWhereHas('asset.product.brand', function ($q) use ($search) {
                        $q->where('name', 'like', $search);
                    })
                    ->orWhereHas('container', function ($q) use ($search) {
                        $q->where('name', 'like', $search);
                    })
                    ->orWhereHas('trip', function ($q) use ($search) {
                        $q->where('trip_number', 'like', $search)
                        ->orWhere('trip_ref', 'like', $search);
                    });
            });
        }

        return $query;
    }
    public function map($fuel): array{

        $horse = $fuel->horse ?? optional($fuel->trip)->horse;
        $equipment = null;
        $makeModel = '';

        if (in_array($fuel->type, ['Horse', 'Trip']) && $horse) {
            $equipment = $horse;
            $makeModel = trim(optional($horse->horse_make)->name . ' ' . optional($horse->horse_model)->name);
        } elseif ($fuel->type == "Vehicle" && $fuel->vehicle) {
            $equipment = $fuel->vehicle;
            $makeModel = trim(optional($fuel->vehicle->vehicle_make)->name . ' ' . optional($fuel->vehicle->vehicle_model)->name);
        } elseif ($fuel->type == "Asset" && $fuel->asset) {
            $makeModel = optional($fuel->asset->product)->name ?? '';
        }

        $category = trim(($fuel->trip ? "Trip#: " . $fuel->trip->trip_number . ' ' : '') . $makeModel);
        $authorizedBy = $fuel->authorized_by_id ? User::find($fuel->authorized_by_id) : null;
        $source = $fuel->source_horse
            ? $fuel->source_horse->identifier_label
            : optional($fuel->container)->name;

        return [
            $fuel->order_number,
            trim(optional($fuel->user)->name . ' ' . optional($fuel->user)->surname),
            $fuel->created_at ? $fuel->created_at->format('Y-m-d H:i') : '',
            $fuel->authorization,
            $authorizedBy ? trim($authorizedBy->name . ' ' . $authorizedBy->surname) : '',
            $source ?? '',
            $fuel->date,
            $fuel->type,
            // reg# / fleet# in the order set in Business Settings
            $equipment ? $equipment->identifier_label : '',
            $category,
            in_array($fuel->type, ['Asset', 'Other']) ? '' : $fuel->odometer,
            $fuel->fillup == 1 ? "initial" : "top up",
            $fuel->container ? $fuel->container->fuel_type : "",
            $fuel->quantity. 'L',
            $fuel->currency ? $fuel->currency->name : "",
            ($fuel->currency ? $fuel->currency->symbol : "") . $fuel->amount,
            $fuel->comments,
        ];
    }

    public function headings(): array{
            return[
                'Order#',
                'Created By',
                'Created On',
                'Auth Status',
                'Authorized By',
                'Source',
                'Date',
                'Fuel Order For',
                'Equipment',
                'Category',
                'Mileage(Kms)',
                'Fuel Order Type',
                'Fuel Type',
                'Quantity',
                'Currency',
                'Amount',
                'Comments',
               
            ];


    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $h = $this->headingRow;

                $sheet->getStyle("A{$h}:Q{$h}")->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ]
                ]);

                $sheet->setCellValue('A7', 'Fuel Summary (fuel & cost figures are for approved orders only)');
                $sheet->mergeCells('A7:Q7');
                $sheet->getStyle('A7')->applyFromArray(['font' => ['bold' => true, 'size' => 14]]);

                $this->writeBlock($sheet, 'B', 8, ['Overview', ''], $this->summary['overview']);
                $this->writeBlock($sheet, 'F', 8, ['Fuel By Source', 'Orders', 'Litres', 'Cost'], $this->summary['stations']);
                $this->writeBlock($sheet, 'K', 8, ['Top Equipment', 'Orders', 'Litres'], $this->summary['topEquipment']);
                $this->writeBlock($sheet, 'O', 8, ['Fuel Order For', 'Orders', 'Litres'], $this->summary['byFor']);
            },
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        if (isset(Auth::user()->employee->company)) {
            $drawing->setName(Auth::user()->employee->company->name);
            $drawing->setDescription(Auth::user()->employee->company->name . 'Logo');
          if (file_exists(public_path('/images/uploads/'.Auth::user()->employee->company->logo))){
            $drawing->setPath(public_path('/images/uploads/'.Auth::user()->employee->company->logo));
        }else{
            $drawing->setPath(public_path('/images/uploads/logo.png'));
        }
            } 
        $drawing->setHeight(90);
        $drawing->setCoordinates('A2');

        return $drawing;
    }

    public function startCell(): string{
        return 'A' . $this->headingRow;
    }

    /** A shaded label/value table starting at $column$row, optional bold header row. */
    protected function writeBlock($sheet, string $column, int $row, ?array $header, array $rows): void
    {
        $width = $header ? count($header) : 2;
        $last = chr(ord($column) + $width - 1);
        $first = $row;

        if ($header) {
            foreach ($header as $i => $text) {
                $sheet->setCellValue(chr(ord($column) + $i) . $row, $text);
            }
            $sheet->getStyle("{$column}{$row}:{$last}{$row}")->applyFromArray(['font' => ['bold' => true]]);
            $row++;
        }

        if (!$rows) {
            $sheet->setCellValue("{$column}{$row}", 'None');
            $row++;
        }

        foreach ($rows as $values) {
            foreach (array_values($values) as $i => $value) {
                $sheet->setCellValue(chr(ord($column) + $i) . $row, $value);
            }
            $row++;
        }

        $sheet->getStyle("{$column}{$first}:{$last}" . ($row - 1))->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FCE4D6'],
            ],
            'borders' => [
                'outline' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ]);
    }
}
