<?php

namespace App\Exports;

use App\Models\Dispatch;
use App\Models\DispatchItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class DispatchesExport implements FromQuery,
ShouldAutoSize,
WithMapping,
WithHeadings,
WithEvents,
WithDrawings,
WithColumnWidths,
WithCustomStartCell
{
    use Exportable;

    protected $department;

    // Summary metrics
    protected int $totalDispatches = 0;
    protected float $totalQty = 0;
    protected array $valueByCurrency = [];
    protected array $statusBreakdown = [];
    protected array $popularStores = [];
    protected array $popularVendors = [];
    protected array $popularBranches = [];

    public function __construct($department = null)
    {
        $this->department = $department;

        $base = $this->baseQuery();

        $this->totalDispatches = (clone $base)->count();

        $this->totalQty = (float) DispatchItem::whereIn('dispatch_id', (clone $base)->pluck('id'))->sum('qty');

        $this->valueByCurrency = (clone $base)
            ->join('currencies', 'currencies.id', '=', 'dispatches.currency_id')
            ->groupBy('currencies.name')
            ->orderByDesc(DB::raw('SUM(dispatches.total)'))
            ->get(['currencies.name as name', DB::raw('SUM(dispatches.total) as total')])
            ->toArray();

        $this->statusBreakdown = (clone $base)
            ->select('authorization', DB::raw('COUNT(*) as total'))
            ->groupBy('authorization')
            ->pluck('total', 'authorization')
            ->toArray();

        $this->popularStores = (clone $base)
            ->whereNotNull('dispatches.store_id')
            ->join('stores', 'stores.id', '=', 'dispatches.store_id')
            ->groupBy('stores.name')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit(5)
            ->get(['stores.name as name', DB::raw('COUNT(*) as total')])
            ->toArray();

        $this->popularVendors = (clone $base)
            ->whereNotNull('dispatches.vendor_id')
            ->join('vendors', 'vendors.id', '=', 'dispatches.vendor_id')
            ->groupBy('vendors.name')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit(5)
            ->get(['vendors.name as name', DB::raw('COUNT(*) as total')])
            ->toArray();

        $this->popularBranches = (clone $base)
            ->whereNotNull('dispatches.branch_id')
            ->join('branches', 'branches.id', '=', 'dispatches.branch_id')
            ->groupBy('branches.name')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit(5)
            ->get(['branches.name as name', DB::raw('COUNT(*) as total')])
            ->toArray();
    }

    protected function baseQuery()
    {
        return Dispatch::query()
            ->when($this->department, function ($q) {
                $q->where('department', $this->department);
            });
    }

    /**
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        return $this->baseQuery()
            ->with([
                'user', 'employee',
                'branch', 'department', 'store', 'currency',
                'ticket', 'horse', 'vehicle', 'trailer', 'vendor',
                'dispatch_items.product', 'dispatch_items.inventory.product',
                'dispatch_items.asset.product', 'dispatch_items.tyre.product',
            ])
            ->orderByDesc('date');
    }

    public function map($dispatch): array
    {
        $requested_by = \App\Models\Employee::find($dispatch->requested_by_id);
        $authorized_by = \App\Models\User::find($dispatch->authorized_by_id);

        $items = $dispatch->dispatch_items->map(function ($dispatch_item) {
            if ($dispatch_item->inventory) {
                return $dispatch_item->inventory->product ? $dispatch_item->inventory->product->name : "";
            } elseif ($dispatch_item->asset) {
                return $dispatch_item->asset->product ? $dispatch_item->asset->product->name : "";
            } elseif ($dispatch_item->tyre) {
                return $dispatch_item->tyre->product ? $dispatch_item->tyre->product->name : "";
            } elseif ($dispatch_item->product) {
                return $dispatch_item->product->name;
            }
            return "";
        })->filter()->implode("\n");

        return [
            $dispatch->dispatch_number,
            $dispatch->date,
            $dispatch->department,
            $dispatch->user ? trim($dispatch->user->name . ' ' . $dispatch->user->surname) : "",
            $requested_by ? trim($requested_by->name . ' ' . $requested_by->surname) : "",
            $dispatch->employee ? trim($dispatch->employee->name . ' ' . $dispatch->employee->surname) : "",
            $dispatch->branch ? $dispatch->branch->name : "",
            $dispatch->store ? $dispatch->store->name : "",
            $dispatch->ticket ? $dispatch->ticket->ticket_number : "",
            $dispatch->horse ? $dispatch->horse->registration_number : "",
            $dispatch->vehicle ? $dispatch->vehicle->registration_number : "",
            $dispatch->trailer ? $dispatch->trailer->registration_number : "",
            $dispatch->vendor ? $dispatch->vendor->name : "",
            $dispatch->description,
            $items,
            $dispatch->dispatch_items->sum('qty'),
            $dispatch->currency ? $dispatch->currency->name : "",
            $dispatch->total ? number_format((float) $dispatch->total, 2, '.', '') : '0.00',
            ucfirst($dispatch->authorization),
            $authorized_by ? trim($authorized_by->name . ' ' . $authorized_by->surname) : "",
            $dispatch->authorization_date,
            $dispatch->authorization_comments,
        ];
    }

    public function headings(): array
    {
        return [
            'Dispatch#',
            'Date',
            'Department',
            'Dispatched By',
            'Requested By',
            'Employee',
            'Branch',
            'Store',
            'Ticket#',
            'Horse',
            'Vehicle',
            'Trailer',
            'Vendor',
            'Narration',
            'Items',
            'Qty',
            'Currency',
            'Total',
            'Status',
            'Authorized By',
            'Authorized On',
            'Comments',
        ];
    }

    /**
     * Cap the width of free-text / multi-value columns so their content
     * wraps and expands the row downwards instead of stretching the column.
     */
    public function columnWidths(): array
    {
        return [
            'N' => 30, // Narration
            'O' => 35, // Items
            'V' => 30, // Comments
        ];
    }

    protected function pairLine(array $rows, string $labelKey = 'name', string $totalKey = 'total'): string
    {
        if (empty($rows)) {
            return 'None';
        }

        return collect($rows)
            ->map(fn ($row) => ($row[$labelKey] ?? 'Unknown') . ' (' . ($row[$totalKey] ?? 0) . ')')
            ->implode(', ');
    }

    protected function valueLine(): string
    {
        if (empty($this->valueByCurrency)) {
            return 'None';
        }

        return collect($this->valueByCurrency)
            ->map(fn ($row) => ($row['name'] ?? '') . ' ' . number_format((float) ($row['total'] ?? 0), 2))
            ->implode(', ');
    }

    protected function statusLine(): string
    {
        if (empty($this->statusBreakdown)) {
            return 'None';
        }

        $pairs = [];
        foreach ($this->statusBreakdown as $status => $count) {
            $pairs[] = ucfirst($status ?: 'pending') . ' ' . (int) $count;
        }

        return implode(', ', $pairs);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Summary block, starting under the logo
                $row = 7;

                $sheet->setCellValue("C{$row}", 'Dispatches Summary');
                $sheet->mergeCells("C{$row}:D{$row}");
                $sheet->getStyle("C{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                ]);
                $row++;

                $summaryStart = $row;

                $sheet->setCellValue("C{$row}", 'Total Dispatches');
                $sheet->setCellValue("D{$row}", $this->totalDispatches);
                $row++;

                $sheet->setCellValue("C{$row}", 'Total Items Dispatched');
                $sheet->setCellValue("D{$row}", $this->totalQty);
                $row++;

                $sheet->setCellValue("C{$row}", 'Total Value Dispatched');
                $sheet->setCellValue("D{$row}", $this->valueLine());
                $row++;

                $sheet->setCellValue("C{$row}", 'By Status');
                $sheet->setCellValue("D{$row}", $this->statusLine());
                $row++;

                $sheet->setCellValue("C{$row}", 'Popular Stores');
                $sheet->setCellValue("D{$row}", $this->pairLine($this->popularStores));
                $row++;

                $sheet->setCellValue("C{$row}", 'Popular Vendors');
                $sheet->setCellValue("D{$row}", $this->pairLine($this->popularVendors));
                $row++;

                $sheet->setCellValue("C{$row}", 'Popular Branches');
                $sheet->setCellValue("D{$row}", $this->pairLine($this->popularBranches));

                $summaryEnd = $row;

                $sheet->getStyle("C{$summaryStart}:D{$summaryEnd}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FCE4D6'],
                    ],
                ]);
                $sheet->getStyle("D{$summaryStart}:D{$summaryEnd}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setWrapText(true);

                // Table headings, two rows below the summary block
                $headingRow = $summaryEnd + 2;
                $sheet->getStyle("A{$headingRow}:V{$headingRow}")->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ]
                ]);

                // Multi-line item lists / narration / comments should wrap and
                // grow the row height downwards rather than widen the column.
                $sheet->getStyle('N:N')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle('O:O')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle('V:V')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            },
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        if (isset(Auth::user()->employee->company)) {
            $drawing->setName(Auth::user()->employee->company->name);
            $drawing->setDescription(Auth::user()->employee->company->name . 'Logo');
            if (file_exists(public_path('/images/uploads/' . Auth::user()->employee->company->logo))) {
                $drawing->setPath(public_path('/images/uploads/' . Auth::user()->employee->company->logo));
            } else {
                $drawing->setPath(public_path('/images/uploads/logo.png'));
            }
        }
        $drawing->setHeight(90);
        $drawing->setCoordinates('A2');

        return $drawing;
    }

    public function startCell(): string
    {
        // Title (row 7) + 7 summary metrics (rows 8-14) + 1 blank row (15) = headings on row 16
        return 'A16';
    }
}
