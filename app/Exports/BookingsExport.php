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
    protected ?int $avgEstimatedTatMinutes = null;
    protected ?int $avgWorkshopTatMinutes = null;
    protected ?int $avgDowntimeTatMinutes = null;

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

                // Average Estimated TAT
        $this->avgEstimatedTatMinutes = (int) ( (clone $base)
            ->where('bookings.status', 0)
            ->whereNotNull('bookings.in_date')->whereNotNull('bookings.in_time')
            ->whereNotNull('bookings.estimated_out_date')->whereNotNull('bookings.estimated_out_time')
            ->whereRaw('TIMESTAMP(bookings.estimated_out_date, bookings.estimated_out_time) >= TIMESTAMP(bookings.in_date, bookings.in_time)')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, TIMESTAMP(bookings.in_date, bookings.in_time), TIMESTAMP(bookings.estimated_out_date, bookings.estimated_out_time))) AS avg_minutes')
            ->value('avg_minutes')
        );

        // Average Workshop TAT (mechanic completion)
        $this->avgWorkshopTatMinutes = (int) ( (clone $base)
            ->where('bookings.status', 0)
            ->whereNotNull('bookings.in_date')->whereNotNull('bookings.in_time')
            ->whereNotNull('bookings.out_date')->whereNotNull('bookings.out_time')
            ->whereRaw('TIMESTAMP(bookings.out_date, bookings.out_time) >= TIMESTAMP(bookings.in_date, bookings.in_time)')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, TIMESTAMP(bookings.in_date, bookings.in_time), TIMESTAMP(bookings.out_date, bookings.out_time))) AS avg_minutes')
            ->value('avg_minutes')
        );

        // Average Total Downtime TAT (until release)
        $this->avgDowntimeTatMinutes = (int) ( (clone $base)
            ->where('bookings.status', 0)
            ->whereNotNull('bookings.in_date')->whereNotNull('bookings.in_time')
            ->whereNotNull('bookings.out_of_workshop_date')->whereNotNull('bookings.out_of_workshop_time')
            ->whereRaw('TIMESTAMP(bookings.out_of_workshop_date, bookings.out_of_workshop_time) >= TIMESTAMP(bookings.in_date, bookings.in_time)')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, TIMESTAMP(bookings.in_date, bookings.in_time), TIMESTAMP(bookings.out_of_workshop_date, bookings.out_of_workshop_time))) AS avg_minutes')
            ->value('avg_minutes')
        );

      
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
        $completion = $booking->out_date ." @ ". $booking->out_time;
        $estimated = $booking->estimated_out_date ." @ ". $booking->estimated_out_time;
        $out_of_workshop = $booking->out_of_workshop_date ." @ ". $booking->out_of_workshop_time;

        return   [
            $booking->booking_number,
            $booking->user->name ." ". $booking->user->surname,
            $booking->employee ? $booking->employee->name : "",
            $assigned_to,
            $booking_for,
            $booking->service_type ? $booking->service_type->name : "",
            $booking->description,
            $date,
            $estimated,
            $completion,
            $out_of_workshop,
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
            'In Date',
            'Estimated Completion',
            'Task Completion',
            'Out of Workshop',
            'Authorization',
            'AuthorizedBy',
            'Status',
        ];
    }

    public function registerEvents(): array
{
    // Precomputed metrics from __construct()
    $total     = $this->totalCount;
    $open      = $this->openCount;
    $closed    = $this->closedCount;
    $assets    = $this->assetCounts;        // ['horse'=>.., 'vehicle'=>.., 'trailer'=>..]
    $services  = $this->serviceBreakdown;   // [['name'=>'Tyres','total'=>12], ...]
    $avgEstimatedTat = $this->avgEstimatedTatMinutes ? $this->formatMinutes($this->avgEstimatedTatMinutes) : 'N/A';
    $avgWorkshopTat  = $this->avgWorkshopTatMinutes  ? $this->formatMinutes($this->avgWorkshopTatMinutes)  : 'N/A';
    $avgDowntimeTat  = $this->avgDowntimeTatMinutes  ? $this->formatMinutes($this->avgDowntimeTatMinutes)  : 'N/A';
    

    // Build a single-line "name count" summary for services (like Asset Type)
    $serviceLine = 'None';
    if (!empty($services)) {
        // e.g. "Tyres 12, Routine 9, Brakes 5"
        $pairs = array_map(
            fn($s) => ($s['name'] ?? 'Unknown').' '.($s['total'] ?? 0),
            $services
        );
        $serviceLine = implode(', ', $pairs);
    }

    return [
        AfterSheet::class => function (AfterSheet $event) use ($total, $open, $closed, $assets, $avgEstimatedTat, $avgWorkshopTat, $avgDowntimeTat,  $serviceLine) {
            $sheet = $event->sheet->getDelegate();

            // 1) Style table headings at row 15 (A..K = 11 cols)
            $sheet->getStyle('A17:N17')->applyFromArray([
                'font' => ['bold' => true],
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                        'color' => ['argb' => 'FFFF0000'],
                    ],
                ],
            ]);

            // 2) Summary block under the logo, starting row 7
            $row = 7;

            // Title (merge across full width)
            $sheet->setCellValue("C{$row}", 'Bookings Summary');
            $sheet->mergeCells("C{$row}:D{$row}");
            $sheet->getStyle("C{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 14],
            ]);
            $row++;

            // Metrics in C (label) / D (value)
            $sheet->setCellValue("C{$row}", 'Total Bookings');
            $sheet->setCellValue("D{$row}", $total);  $row++;

            $sheet->setCellValue("C{$row}", 'Open Bookings');
            $sheet->setCellValue("D{$row}", $open);   $row++;

            $sheet->setCellValue("C{$row}", 'Closed Bookings');
            $sheet->setCellValue("D{$row}", $closed); $row++;

            $sheet->setCellValue("C{$row}", 'Estimated Avg TAT');
            $sheet->setCellValue("D{$row}", $avgEstimatedTat); $row++;

            $sheet->setCellValue("C{$row}", 'Workshop Avg TAT');
            $sheet->setCellValue("D{$row}", $avgWorkshopTat);  $row++;

            $sheet->setCellValue("C{$row}", 'Downtime Avg TAT');
            $sheet->setCellValue("D{$row}", $avgDowntimeTat);  $row++;

            // By Asset Type (inline)
            $sheet->setCellValue("C{$row}", 'By Asset Type');
            $sheet->setCellValue(
                "D{$row}",
                sprintf(
                    'Horse %d, Vehicle %d, Trailer %d',
                    $assets['horse'] ?? 0,
                    $assets['vehicle'] ?? 0,
                    $assets['trailer'] ?? 0
                )
            );
            $row++;

            // By Service Type (inline, like Asset Type)
            $sheet->setCellValue("C{$row}", 'By Service Type');
            $sheet->setCellValue("D{$row}", $serviceLine);

            // Style: bold + light fill for C/D block, left-align values in D
            $sheet->getStyle("C8:D{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FCE4D6'],
                ],
            ]);
            $sheet->getStyle("D8:D{$row}")
                  ->getAlignment()
                  ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
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
        return 'A17';
    }
}
