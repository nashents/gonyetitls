<?php

namespace App\Exports;

use App\Http\Livewire\Concerns\FiltersAuthStatus;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use Carbon\Carbon;
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

    // Filters from the dispatches table (see applyFilters):
    // from, to, dispatch_filter (date column), search, auth_status
    protected array $filters = [];

    // Summary metrics
    protected int $totalDispatches = 0;
    protected float $totalQty = 0;
    protected array $valueByCurrency = [];
    protected array $statusBreakdown = [];
    protected array $popularStores = [];
    protected array $popularVendors = [];
    protected array $popularBranches = [];
    protected array $itemsDispatched = [];

    public function __construct($department = null, array $filters = [])
    {
        $this->department = $department;
        $this->filters = $filters;

        $base = $this->baseQuery();

        $dispatchIds = (clone $base)->pluck('dispatches.id');

        $this->totalDispatches = $dispatchIds->count();

        $this->totalQty = (float) DispatchItem::whereIn('dispatch_id', $dispatchIds)->sum('qty');

        $this->valueByCurrency = (clone $base)
            ->join('currencies', 'currencies.id', '=', 'dispatches.currency_id')
            ->groupBy('currencies.name')
            ->orderByDesc(DB::raw('SUM(dispatches.total)'))
            ->get(['currencies.name as name', DB::raw('SUM(dispatches.total) as total')])
            ->toArray();

        $this->statusBreakdown = (clone $base)
            ->select('dispatches.authorization', DB::raw('COUNT(*) as total'))
            ->groupBy('dispatches.authorization')
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

        $this->itemsDispatched = $this->buildItemsDispatched($dispatchIds);
    }

    protected function baseQuery()
    {
        return static::applyFilters(
            Dispatch::query()->when($this->department, function ($q) {
                $q->where('dispatches.department', $this->department);
            }),
            $this->filters
        );
    }

    /**
     * Date range / search / auth status exactly as the dispatches table
     * applies them (Dispatches\Index::render uses this too), so the export
     * matches what the user is looking at.
     */
    public static function applyFilters($query, array $filters)
    {
        $column = in_array($filters['dispatch_filter'] ?? null, ['created_at', 'date'], true)
            ? $filters['dispatch_filter']
            : 'created_at';
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $search = $filters['search'] ?? null;

        if (filled($from) && filled($to)) {
            $query->whereDate("dispatches.{$column}", '>=', $from)
                ->whereDate("dispatches.{$column}", '<=', $to);
        } elseif (! filled($search)) {
            // Default "this month", lifted while searching so matches
            // outside the current period (e.g. by product) still show.
            $query->whereMonth("dispatches.{$column}", Carbon::now()->month)
                ->whereYear("dispatches.{$column}", Carbon::now()->year);
        }

        if (filled($search)) {
            $term = '%' . $search . '%';
            $query->where(function ($qq) use ($term) {
                $qq->where('dispatches.dispatch_number', 'like', $term)
                    ->orWhere('dispatches.authorization', 'like', $term)
                    ->orWhere('dispatches.date', 'like', $term)
                    ->orWhereHas('ticket', fn ($sub) => $sub->where('ticket_number', 'like', $term))
                    ->orWhereHas('horse', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term)
                            ->orWhere('fleet_number', 'like', $term);
                    })
                    ->orWhereHas('store', fn ($sub) => $sub->where('name', 'like', $term))
                    ->orWhereHas('vehicle', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term)
                            ->orWhere('fleet_number', 'like', $term);
                    })
                    ->orWhereHas('trailer', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term)
                            ->orWhere('fleet_number', 'like', $term);
                    })
                    ->orWhereHas('employee', fn ($sub) => $sub->where(DB::raw("concat(name, ' ', surname)"), 'like', $term))
                    ->orWhereHas('dispatch_items.product', function ($sub) use ($term) {
                        $sub->where('product_number', 'like', $term)
                            ->orWhere('name', 'like', $term)
                            ->orWhere('identification_number', 'like', $term);
                    })
                    ->orWhereHas('dispatch_items.product.brand', fn ($sub) => $sub->where('name', 'like', $term))
                    ->orWhereHas('dispatch_items.inventory', function ($sub) use ($term) {
                        $sub->where('inventory_number', 'like', $term)
                            ->orWhere('serial_number', 'like', $term);
                    })
                    ->orWhereHas('dispatch_items.tyre', function ($sub) use ($term) {
                        $sub->where('tyre_number', 'like', $term)
                            ->orWhere('serial_number', 'like', $term);
                    })
                    ->orWhereHas('dispatch_items.asset', function ($sub) use ($term) {
                        $sub->where('asset_number', 'like', $term)
                            ->orWhere('serial_number', 'like', $term);
                    });
            });
        }

        FiltersAuthStatus::filterByAuthStatus($query, $filters['auth_status'] ?? null, 'dispatches.authorization');

        return $query;
    }

    /** The product behind a dispatch line (inventory / asset / tyre / direct product). */
    protected static function itemProduct($dispatch_item)
    {
        if ($dispatch_item->inventory) {
            return $dispatch_item->inventory->product;
        }
        if ($dispatch_item->asset) {
            return $dispatch_item->asset->product;
        }
        if ($dispatch_item->tyre) {
            return $dispatch_item->tyre->product;
        }

        return $dispatch_item->product;
    }

    /** "D00009 Sinopec 15W40 Diesel Engine Oil (200L)" */
    protected static function itemLabel($product): string
    {
        if (! $product) {
            return 'Unknown item';
        }

        return trim(($product->product_number ? $product->product_number . ' ' : '') . $product->name);
    }

    protected static function formatQty($qty): string
    {
        return rtrim(rtrim(number_format((float) $qty, 2, '.', ''), '0'), '.');
    }

    /** Total qty dispatched per item across the filtered dispatches, largest first. */
    protected function buildItemsDispatched($dispatchIds): array
    {
        return DispatchItem::whereIn('dispatch_id', $dispatchIds)
            ->with(['product', 'inventory.product', 'asset.product', 'tyre.product'])
            ->get()
            ->groupBy(fn ($item) => static::itemLabel(static::itemProduct($item)))
            ->map(fn ($items, $label) => ['name' => $label, 'total' => static::formatQty($items->sum('qty')), 'sort' => (float) $items->sum('qty')])
            ->sortByDesc('sort')
            ->values()
            ->all();
    }

    /** "Generated on ... by ... Applied filters: ..." line, same format as the Trips report. */
    protected function reportMetaText(): string
    {
        $filters = [];

        if ($this->department) {
            $filters[] = 'Department: ' . ucfirst($this->department);
        }

        $column = ($this->filters['dispatch_filter'] ?? 'created_at') === 'date' ? 'Dispatch Date' : 'Dispatch Created At';
        $from = $this->filters['from'] ?? null;
        $to = $this->filters['to'] ?? null;
        $search = $this->filters['search'] ?? null;

        if (filled($from) && filled($to)) {
            $filters[] = "{$column}: " . Carbon::parse($from)->format('d M Y') . ' to ' . Carbon::parse($to)->format('d M Y');
        } elseif (! filled($search)) {
            $filters[] = "{$column}: " . now()->format('F Y');
        } else {
            $filters[] = "{$column}: All dates";
        }

        if (filled($search)) {
            $filters[] = 'Search: ' . $search;
        }

        $status = $this->filters['auth_status'] ?? null;
        $filters[] = 'Auth Status: ' . (filled($status) ? ucfirst($status) : 'All');

        $user = Auth::user();

        return 'This report was generated on ' . now()->format('d M Y H:i')
            . ' by ' . trim(($user->name ?? '') . ' ' . ($user->surname ?? ''))
            . '. Applied filters: ' . implode(' | ', $filters) . '.';
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
            ->orderByDesc('dispatches.date')
            ->orderByDesc('dispatches.id');
    }

    public function map($dispatch): array
    {
        $requested_by = \App\Models\Employee::find($dispatch->requested_by_id);
        $authorized_by = \App\Models\User::find($dispatch->authorized_by_id);

        $items = $dispatch->dispatch_items
            ->map(fn ($dispatch_item) => static::itemLabel(static::itemProduct($dispatch_item)) . ' (' . static::formatQty($dispatch_item->qty) . ')')
            ->implode("\n");

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
            $dispatch->horse ? $dispatch->horse->identifier_label : "",
            $dispatch->vehicle ? $dispatch->vehicle->identifier_label : "",
            $dispatch->trailer ? $dispatch->trailer->identifier_label : "",
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
            'Items (Qty)',
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
     * D also holds the summary values (popular stores, items dispatched...).
     */
    public function columnWidths(): array
    {
        return [
            'D' => 50, // Dispatched By + summary values
            'N' => 30, // Narration
            'O' => 45, // Items (Qty)
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

                // Generated on / by + applied filters (same line as the Trips report)
                $sheet->mergeCells('A1:V1');
                $sheet->setCellValue('A1', $this->reportMetaText());
                $sheet->getStyle('A1:V1')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);

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
                $row++;

                // Item + total qty dispatched, e.g. "D00009 Sinopec 15W40 ... (1000), A00003 ... (500)"
                $sheet->setCellValue("C{$row}", 'Items Dispatched (Qty)');
                $sheet->setCellValue("D{$row}", $this->pairLine($this->itemsDispatched));

                $summaryEnd = $row;

                $sheet->getStyle("C{$summaryStart}:D{$summaryEnd}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FCE4D6'],
                    ],
                    'alignment' => ['vertical' => Alignment::VERTICAL_TOP],
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
                $sheet->getStyle('D:D')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
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
        // Title (row 7) + 8 summary metrics (rows 8-15) + 1 blank row (16) = headings on row 17
        return 'A17';
    }
}
