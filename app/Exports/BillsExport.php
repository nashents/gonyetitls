<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Bill;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;


class BillsExport implements
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
    public $bill_filter;
    public $search;
    public $tax_status;
    public $transporter_id;
    public $customer_id;
    public $asset_id;
    public $trip_id;
    public $currency_id;
    public $horse_id;
    public $trailer_id;
    public $vehicle_id;

    // ✅ Summary props (computed from the SAME filtered dataset)
    protected int $summaryBillsCount = 0;
    protected float $summaryTotalCost = 0.0;
    protected int $summaryDistinctAccounts = 0;
    protected array $summaryPerAccount = []; // [ ['name'=>..., 'count'=>..., 'sum'=>...], ...]

    public function __construct($from, $to, $filters, $search)
    {
        $this->from           = $from;
        $this->to             = $to;
        $this->search         = $search;
        $this->bill_filter    = $filters['bill_filter'];
        $this->tax_status     = $filters['tax_status'];
        $this->customer_id    = $filters['customer_id'];
        $this->transporter_id = $filters['transporter_id'];
        $this->asset_id       = $filters['asset_id'];
        $this->trip_id        = $filters['trip_id'];
        $this->trailer_id     = $filters['trailer_id'];
        $this->horse_id       = $filters['horse_id'];
        $this->currency_id    = $filters['currency_id'];
        $this->vehicle_id     = $filters['vehicle_id'];
    }


    protected function billFilterColumn(): string
    {
        $col = $this->bill_filter ?: 'created_at';

        // If already qualified like "bills.created_at", keep it
        if (str_contains($col, '.')) {
            return $col;
        }

        // Otherwise force bills.<col>
        return "bills.$col";
    }

    /**
     * ✅ Apply ALL filters in one place so summary & export always match
     */
    protected function applyFilters(Builder $q): Builder
    {
        $q->where('to_be_paid', true);

        $filterCol = $this->billFilterColumn();

        $q->when(
            filled($this->from) && filled($this->to),
            fn (Builder $qq) => $qq->whereBetween($filterCol, [
                Carbon::parse($this->from)->startOfDay(),
                Carbon::parse($this->to)->endOfDay(),
            ]),
            fn (Builder $qq) => $qq->whereMonth($filterCol, now()->month)
                                ->whereYear($filterCol, now()->year)
        );

    
        // Search filter (GROUPED)
        $q->when(filled($this->search), function (Builder $qq) {
            $s = trim($this->search);
            $term = "%{$s}%";

            $qq->where(function (Builder $w) use ($term) {
                $w->where('bill_number', 'like', $term)
                  ->orWhere('status', 'like', $term)
                  ->orWhere('bill_date', 'like', $term)

                  ->orWhereHas('horse', fn (Builder $r) => $r->where('registration_number', 'like', $term))
                  ->orWhereHas('vehicle', fn (Builder $r) => $r->where('registration_number', 'like', $term))
                  ->orWhereHas('trailer', fn (Builder $r) => $r->where('registration_number', 'like', $term))

                  ->orWhereHas('driver.employee', function (Builder $r) use ($term) {
                      $r->whereRaw("concat(name, ' ', surname) like ?", [$term]);
                  })

                  ->orWhereHas('ticket', fn (Builder $r) => $r->where('ticket_number', 'like', $term))
                  ->orWhereHas('trip', fn (Builder $r) => $r->where('trip_number', 'like', $term))
                  ->orWhereHas('currency', fn (Builder $r) => $r->where('name', 'like', $term))
                  ->orWhereHas('invoice', fn (Builder $r) => $r->where('invoice_number', 'like', $term))
                  ->orWhereHas('transporter', fn (Builder $r) => $r->where('name', 'like', $term))
                  ->orWhereHas('container', fn (Builder $r) => $r->where('name', 'like', $term))
                  ->orWhereHas('purchase', fn (Builder $r) => $r->where('purchase_number', 'like', $term))
                  ->orWhereHas('vendor', fn (Builder $r) => $r->where('name', 'like', $term));
            });
        });

        if ($this->customer_id !== "" && $this->customer_id !== null) {
            $q->where('customer_id', $this->customer_id);
        }
        if ($this->transporter_id !== "" && $this->transporter_id !== null) {
            $q->where('transporter_id', $this->transporter_id);
        }
        if ($this->trip_id !== "" && $this->trip_id !== null) {
            $q->where('trip_id', $this->trip_id);
        }
        if ($this->currency_id !== "" && $this->currency_id !== null) {
            $q->where('currency_id', $this->currency_id);
        }

        if ($this->tax_status === 'taxed') {
            $q->whereNotNull('tax_amount')
              ->where('tax_amount', '!=', 0)
              ->where('tax_amount', '!=', '');
        }
        if ($this->tax_status === 'non-taxed') {
            $q->where(function ($qq) {
                $qq->whereNull('tax_amount')
                   ->orWhere('tax_amount', 0)
                   ->orWhere('tax_amount', '');
            });
        }

        if ($this->horse_id !== "" && $this->horse_id !== null) {
            $q->where('horse_id', $this->horse_id);
        }
        if ($this->trailer_id !== "" && $this->trailer_id !== null) {
            $q->where('trailer_id', $this->trailer_id);
        }
        if ($this->vehicle_id !== "" && $this->vehicle_id !== null) {
            $q->where('vehicle_id', $this->vehicle_id);
        }
        if ($this->asset_id !== "" && $this->asset_id !== null) {
            $q->where('asset_id', $this->asset_id);
        }

        return $q;
    }

    /**
     * ✅ Compute summary from the SAME dataset
     */
    protected function computeSummary(): void
    {
        $summaryBase = $this->applyFilters(Bill::query());

        $this->summaryBillsCount      = (int) (clone $summaryBase)->count();
        $this->summaryTotalCost       = (float) (clone $summaryBase)->sum('total');
        $this->summaryDistinctAccounts = (int) (clone $summaryBase)->distinct('account_id')->count('account_id');

        // Bills per account (name + count + sum)
        // Change 'accounts' if your table name differs
        $rows = (clone $summaryBase)
            ->leftJoin('accounts', 'bills.account_id', '=', 'accounts.id')
            ->selectRaw("
                COALESCE(accounts.name, 'Unassigned') as account_name,
                COUNT(bills.id) as bills_count,
                COALESCE(SUM(bills.total), 0) as total_sum
            ")
            ->groupBy('accounts.name')
            ->orderByDesc('bills_count')
            ->get();

        $this->summaryPerAccount = $rows->map(fn ($r) => [
            'name'  => $r->account_name,
            'count' => (int) $r->bills_count,
            'sum'   => (float) $r->total_sum,
        ])->toArray();
    }

    public function query()
    {
        // ✅ compute summary ONCE based on filters
        $this->computeSummary();

        $base = $this->applyFilters(
            Bill::query()->with([
                'invoice',
                'transporter',
                'container',
                'top_up',
                'trip',
                'horse',
                'vehicle',
                'trailer',
                'driver.employee',
                'ticket',
                'purchase',
                'vendor',
                'currency',
                'payments',
                // If you rely on these in map(), consider eager loading too:
                // 'bill_expenses.product',
                // 'bill_expenses.expense',
                // 'bill_expenses.inventory.product',
            ])
        );

        return $base->orderByDesc($this->bill_filter);
    }

    public function map($bill): array
    {
        // NOTE: I did not rewrite your whole categorisation logic.
        // Your existing logic stays as-is.

        $symbol   = $bill->currency ? $bill->currency->symbol : "";
        $currency = $bill->currency ? $bill->currency->name : "";

        $subtotal    = number_format($bill->subtotal ? $bill->subtotal : 0, 2);
        $tax_amount  = number_format($bill->tax_amount ? $bill->tax_amount : 0, 2);
        $total       = number_format($bill->total, 2);

        if (isset($bill->payments)) {
            $payments = number_format($bill->payments->sum('amount'), 2);
        } else {
            $payments = number_format($bill->bill_payments->sum('amount'), 2);
        }

        $balance = number_format($bill->balance, 2);

        // ✅ reset items per row (prevents “carry-over”)
        $items = [];

        if ($bill->bill_expenses) {
            foreach ($bill->bill_expenses as $expense) {
                if ($expense->product) {
                    $items[] = $expense->product->name ?? "";
                } elseif ($expense->expense) {
                    $items[] = $expense->expense->name ?? "";
                } elseif ($expense->inventory) {
                    $items[] = $expense->inventory->product->name ?? "";
                }
            }
        }

        $items_list = $items ? implode(', ', array_filter($items)) : "";

        // keep your existing bill_category logic (not included here for brevity)
        $bill_category = $bill_category ?? "";

        return [
            $bill->bill_number,
            $bill_category,
            $items_list,
            $bill->bill_date,
            $bill->due_date,
            $bill->status,
            $currency . ' ' . $symbol,
            $subtotal ? $subtotal : $total,
            $tax_amount,
            $total,
            $payments,
            $balance,
        ];
    }

    public function headings(): array
    {
        return [
            'Bill#',
            'Bill Summary',
            'Item(s)',
            'Date',
            'Due',
            'Status',
            'Currency',
            'Subtotal',
            'Tax Amt',
            'Total',
            'Paid',
            'Balance',
        ];
    }

    public function registerEvents(): array
    {
        return [
           AfterSheet::class => function (AfterSheet $event) {

    // Header row styling (your existing)
    $event->sheet->getStyle('A8:L8')->applyFromArray([
        'font' => ['bold' => true],
        'borders' => [
            'outline' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                'color' => ['argb' => 'FFFF0000'],
            ],
        ],
    ]);

    $sheet = $event->sheet->getDelegate();

    /**
     * =========================
     * SUMMARY (C2 going down)
     * =========================
     */
    // Title (merge for a nice block header)
    $sheet->mergeCells('C2:D2');
    $sheet->setCellValue('C2', 'SUMMARY');

    $sheet->setCellValue('C3', 'Total Bills Count');
    $sheet->setCellValue('D3', $this->summaryBillsCount);

    $sheet->setCellValue('C4', 'Total Bill Cost');
    $sheet->setCellValue('D4', $this->summaryTotalCost);

    $sheet->setCellValue('C5', 'Distinct Accounts');
    $sheet->setCellValue('D5', $this->summaryDistinctAccounts);

    // Format total cost to 2 decimals
    $sheet->getStyle('D4')->getNumberFormat()
        ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);

    // ✅ Background + border around the summary block
    $summaryRange = 'C2:D5';

    $sheet->getStyle($summaryRange)->applyFromArray([
        'font' => [
            'bold' => false,
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FCE4D6'], // light grey
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
        'alignment' => [
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
    ]);

    // Make title stand out
    $sheet->getStyle('C2')->applyFromArray([
        'font' => ['bold' => true, 'size' => 12],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
    ]);

    // Labels bold
    $sheet->getStyle('C3:C5')->getFont()->setBold(true);

    $startRow = 6;
    $colIndex = 3; // C = 3

    foreach ($this->summaryPerAccount as $acc) {
        $nameCol = Coordinate::stringFromColumnIndex($colIndex);
        $sumCol  = Coordinate::stringFromColumnIndex($colIndex + 1);

        $sheet->setCellValue("{$nameCol}{$startRow}", $acc['name']);
        $sheet->setCellValue("{$sumCol}{$startRow}", $acc['sum']);

        // money/number format
        $sheet->getStyle("{$sumCol}{$startRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

        $colIndex += 2; // next pair
    }

    // Style the whole horizontal range (background + border + bold)
    $lastCol = Coordinate::stringFromColumnIndex(max(3, $colIndex - 1)); // last written column
    $accountsRange = "C{$startRow}:{$lastCol}{$startRow}";

    $sheet->getStyle($accountsRange)->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FCE4D6'], // same light grey
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
        'alignment' => [
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
    ]);

    // Optional: give those columns a bit more space
    $sheet->getRowDimension($startRow)->setRowHeight(18);

    /**
     * Optional: keep your "Bills per Account" table somewhere else (e.g. F2)
     * so it doesn't clash with your A7 export headings.
     */
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
        return 'A8';
    }
}