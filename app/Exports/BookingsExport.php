<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
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

class BookingsExport implements
    FromQuery,
    ShouldAutoSize,
    WithMapping,
    WithHeadings,
    WithEvents,
    WithDrawings,
    WithCustomStartCell
{
    use Exportable;

    public $search;
    public $from;
    public $to;
    public $booking_status;
    public $filter;
    public $search_id;

    // Summary
    protected int $totalCount = 0;
    protected int $openCount = 0;
    protected int $closedCount = 0;
    protected array $assetCounts = ['horse' => 0, 'vehicle' => 0, 'trailer' => 0];
    protected array $serviceBreakdown = []; // [['name' => 'Tyres', 'total' => 12], ...]
    protected ?int $avgTatMinutes = null;

    // Layout
    protected int $tableStartRow = 10; // computed dynamically

    public function __construct($booking_status = null, $search = null, $from = null, $to = null, $filter = null, $search_id = null)
    {
        $this->booking_status = $booking_status;
        $this->search         = $search;
        $this->from           = $from;
        $this->to             = $to;
        $this->filter         = $filter;
        $this->search_id      = $search_id;

        // Pre-compute metrics on the base (status-agnostic) query
        $base = $this->commonQuery(); // date + filter + search, no status
        $this->totalCount  = (clone $base)->count();
        $this->openCount   = (clone $base)->where('bookings.status', 1)->count();
        $this->closedCount = (clone $base)->where('bookings.status', 0)->count();

        // asset-type counts
        $this->assetCounts['horse']   = (clone $base)->whereNotNull('bookings.horse_id')->count();
        $this->assetCounts['vehicle'] = (clone $base)->whereNotNull('bookings.vehicle_id')->count();
        $this->assetCounts['trailer'] = (clone $base)->whereNotNull('bookings.trailer_id')->count();
        $this->assetCounts['asset'] = (clone $base)->whereNotNull('bookings.asset_id')->count();

        // service-type breakdown (qualify created_at already handled in commonQuery)
        $this->serviceBreakdown = (clone $base)
            ->join('service_types', 'service_types.id', '=', 'bookings.service_type_id')
            ->whereNotNull('bookings.service_type_id')
            ->groupBy('service_types.name')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->get([
                'service_types.name as name',
                DB::raw('COUNT(*) as total'),
            ])
            ->toArray();

        // avg TAT (qualify columns inside TIMESTAMPDIFF)
        $this->avgTatMinutes = (int) ((clone $base)
            ->where('bookings.status', 0) // 0 = Closed
            ->whereNotNull('bookings.in_date')->whereNotNull('bookings.in_time')
            ->whereNotNull('bookings.out_date')->whereNotNull('bookings.out_time')
            ->value(DB::raw(
                'AVG(TIMESTAMPDIFF(MINUTE, CONCAT(bookings.in_date," ",bookings.in_time), CONCAT(bookings.out_date," ",bookings.out_time)))'
            )));

        // Compute dynamic start row:
        // Rows used:
        // 6: Title, 7-9: totals, 10: asset line, 11..(10 + svcCount - 1): service lines, then one row for Avg TAT
        $svcRows = max(1, count($this->serviceBreakdown)); // at least one row reserved
        $lastSummaryRow = 10 + $svcRows; // 6 title + 3 totals + 1 asset + svcRows
        $lastSummaryRow += 1; // +1 for Avg TAT line
        $this->tableStartRow = $lastSummaryRow + 2; // spacer row
    }

    /**
     * Base filters shared by summary and table (date + filter + search)
     */
    protected function commonQuery(): Builder
    {
        $query = Booking::query()
            ->with(['ticket','inspection','horse','trailer','vehicle','service_type']);

        // Date window
        if (!empty($this->from) && !empty($this->to)) {
            $from = Carbon::parse($this->from)->startOfDay();
            $to   = Carbon::parse($this->to)->endOfDay();
            $query->whereBetween('bookings.created_at', [$from, $to]);
        } else {
            $query->whereBetween('bookings.created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        }

        // Specific subject filter
        if (!empty($this->filter) && !empty($this->search_id)) {
            $column = match ($this->filter) {
                'horse'   => 'horse_id',
                'trailer' => 'trailer_id',
                'vehicle' => 'vehicle_id',
                'asset'   => 'asset_id',
                default   => null,
            };
            if ($column) {
                $query->where("bookings.$column", $this->search_id);
            }
        }

        // Text search
        if (filled($this->search)) {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('bookings.booking_number', 'like', $term)
                ->orWhereHas('horse', fn($qq) => $qq->where('registration_number', 'like', $term)
                                                    ->orWhere('fleet_number', 'like', $term))
                ->orWhereHas('vehicle', fn($qq) => $qq->where('registration_number', 'like', $term)
                                                        ->orWhere('fleet_number', 'like', $term))
                ->orWhereHas('trailer', fn($qq) => $qq->where('registration_number', 'like', $term)
                                                        ->orWhere('fleet_number', 'like', $term))
                ->orWhereHas('ticket', fn($qq) => $qq->where('ticket_number', 'like', $term))
                ->orWhereHas('inspection', fn($qq) => $qq->where('inspection_number', 'like', $term));
            });
        }

        return $query;
    }

    /**
     * Table data (adds booking_status filter)
     */
    public function query(): Builder
    {
        $query = $this->commonQuery();

        if ($this->booking_status !== 'all' && $this->booking_status !== null && $this->booking_status !== '') {
            $query->where('bookings.status', $this->booking_status);
        }

        return $query->orderByDesc('bookings.created_at');
    }

    public function map($booking): array
    {
        // ... your existing map() unchanged ...
        // (keeping what you had; omitted here for brevity)
        // Make sure you didn't remove the body of map() in your file.

        // ----- BEGIN original body (paste yours here) -----
        if (isset($booking->employees) && $booking->employees->count()>0){
            foreach ($booking->employees as $mechanic){
              $mechanics[] =  $mechanic->name." ".$mechanic->surname; 
            }
            $assigned_to = isset($mechanics) ? implode(',',$mechanics) : "";
        } elseif(isset($booking->vendor)){
            $assigned_to = ucfirst($booking->vendor->name);
        } else {
            $assigned_to = "";
        }

        if (isset($booking->horse)){
            $booking_for = "Horse | ". ucfirst($booking->horse->horse_make ? $booking->horse->horse_make->name : "") ." ". ucfirst($booking->horse->horse_model ? $booking->horse->horse_model->name : "" ) ." ".  ucfirst($booking->horse ? $booking->horse->registration_number : "") ." ". ucfirst($booking->horse ? "| ".$booking->horse->fleet_number : "");
        } elseif(isset($booking->vehicle)){
            $booking_for = "Vehicle | ". ucfirst($booking->vehicle->vehicle_make ? $booking->vehicle->vehicle_make->name : "") ." ".  ucfirst($booking->vehicle->vehicle_model ? $booking->vehicle->vehicle_model->name : "") ." ". ucfirst($booking->vehicle ? $booking->vehicle->registration_number : "") . " " . ucfirst($booking->vehicle ? "| ".$booking->vehicle->fleet_number : "");
        } elseif(isset($booking->trailer)){
            $booking_for = "Trailer | ". ucfirst($booking->trailer ? $booking->trailer->make : "") ." ". ucfirst($booking->trailer ? $booking->trailer->model : "") ." ". ucfirst($booking->trailer ? $booking->trailer->registration_number : "") ." ". ucfirst($booking->trailer ? "| ".$booking->trailer->fleet_number : "");
        } else {
            $booking_for = "";
        }

        $user = User::find($booking->authorized_by_id);
        $authorized_by = $user ? $user->name ." ". $user->surname : "";

        $date = $booking->in_date ." @ ". $booking->in_time;

        return   [
            $booking->booking_number,
            $booking->user->name ." ". $booking->user->surname,
            $booking->employee ? $booking->employee->name : "",
            $assigned_to,
            $booking_for,
            $booking->service_type ? $booking->service_type->name : "",
            $booking->description,
            $date,
            $booking->authorization,
            $authorized_by,
            $booking->status == 1 ? "Open" : "Closed",
        ];
        // ----- END original body -----
    }

    public function headings(): array
    {
        return [
            'Booking#',
            'CreatedBy',
            'RequestedBy',
            'AssignedTo',
            'BookingFor',
            'Service Type',
            'Narration',
            'Date',
            'Authorization',
            'AuthorizedBy',
            'Status',
        ];
    }

    public function registerEvents(): array
    {
        $total   = $this->totalCount;
        $open    = $this->openCount;
        $closed  = $this->closedCount;
        $assets  = $this->assetCounts;
        $services = $this->serviceBreakdown;
        $avgMin  = $this->avgTatMinutes;
        $tableRow = $this->tableStartRow;

        return [
            AfterSheet::class => function (AfterSheet $event) use ($total, $open, $closed, $assets, $services, $avgMin, $tableRow) {
                $sheet = $event->sheet->getDelegate();

                // ---- Summary header (Row 6) ----
                $sheet->mergeCells('A6:K6');
                $sheet->setCellValue('A6', 'Bookings Summary');
                $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // ---- Totals (Rows 7-9) ----
                $sheet->setCellValue('A7', 'Total Bookings:');  $sheet->setCellValue('B7', $total);
                $sheet->setCellValue('A8', 'Open Bookings:');   $sheet->setCellValue('B8', $open);
                $sheet->setCellValue('A9', 'Closed Bookings:'); $sheet->setCellValue('B9', $closed);
                $sheet->getStyle('A7:A9')->getFont()->setBold(true);

                // ---- By Asset Type (Row 10) ----
                $assetLine = sprintf(
                    'By Asset Type: Horse %d, Vehicle %d, Trailer %d',
                    $assets['horse'] ?? 0, $assets['vehicle'] ?? 0, $assets['trailer'] ?? 0
                );
                $sheet->setCellValue('A10', $assetLine);
                $sheet->mergeCells('A10:K10');

                // ---- Service Type Breakdown (Rows 11..)
                $svcRow = 11;
                if (!empty($services)) {
                    $sheet->setCellValue("A{$svcRow}", 'By Service Type:');
                    $sheet->getStyle("A{$svcRow}")->getFont()->setBold(true);
                    $svcRow++;

                    foreach ($services as $svc) {
                        $sheet->setCellValue("A{$svcRow}", $svc['name'] ?? 'Unknown');
                        $sheet->setCellValue("B{$svcRow}", $svc['total'] ?? 0);
                        $svcRow++;
                    }
                } else {
                    $sheet->setCellValue("A{$svcRow}", 'By Service Type: None');
                    $svcRow++;
                }

                // ---- Average TAT (next row)
                $tatText = 'Average TAT: ' . ($avgMin ? $this->formatMinutes($avgMin) : 'N/A');
                $sheet->setCellValue("A{$svcRow}", $tatText);
                $sheet->mergeCells("A{$svcRow}:K{$svcRow}");

                // ---- Table Headings styling at dynamic start row
                $sheet->getStyle("A{$tableRow}:K{$tableRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ],
                ]);
            },
        ];
    }

    // Helper for human-readable duration (e.g., "1d 3h 20m")
    protected function formatMinutes(int $minutes): string
    {
        if ($minutes <= 0) return '0m';
        $days = intdiv($minutes, 1440);
        $minutes -= $days * 1440;
        $hours = intdiv($minutes, 60);
        $minutes -= $hours * 60;

        $parts = [];
        if ($days)  $parts[] = "{$days}d";
        if ($hours) $parts[] = "{$hours}h";
        if ($minutes) $parts[] = "{$minutes}m";
        return implode(' ', $parts);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        if (isset(Auth::user()->employee->company)) {
            $drawing->setName(Auth::user()->employee->company->name);
            $drawing->setDescription(Auth::user()->employee->company->name . ' Logo');
            if (file_exists(public_path('/images/uploads/'.Auth::user()->employee->company->logo))) {
                $drawing->setPath(public_path('/images/uploads/'.Auth::user()->employee->company->logo));
            } else {
                $drawing->setPath(public_path('/images/uploads/logo.png'));
            }
        }
        $drawing->setHeight(90);
        $drawing->setCoordinates('A2'); // rows 1–5 area
        return $drawing;
    }

    public function startCell(): string
    {
        // Table starts after dynamic summary block
        return 'A' . $this->tableStartRow;
    }
}
