<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SalesExport implements
    FromQuery,
    // ShouldAutoSize,
    WithMapping,
    WithHeadings,
    WithEvents,
    WithDrawings,
    WithCustomStartCell
{
    use Exportable;

    public $from;
    public $to;
    public $invoice_filter;
    public $search;
    public $tax_status;
    public $transporter_id;
    public $customer_id;
    public $currency_id;

    // ✅ Summary props
    protected int $summaryInvoicesCount = 0;
    protected float $summaryRevenue = 0.0;
    protected float $summaryDue = 0.0;
    protected float $summaryPaid = 0.0;
    protected array $summaryByCurrency = []; 

    // ✅ Owing per customer/transporter (horizontal row)
    // Each item: ['name' => 'AFC', 'sum' => 200.00]
    protected array $owingRecipients = [];

    public function __construct($from, $to, $filters, $search)
    {
        $this->from           = $from;
        $this->to             = $to;
        $this->invoice_filter = $filters['invoice_filter'];
        $this->search         = $search;
        $this->tax_status     = $filters['tax_status'];
        $this->customer_id    = $filters['customer_id'];
        $this->transporter_id = $filters['transporter_id'];
        $this->currency_id    = $filters['currency_id'];
    }

    /**
     * ✅ Always qualify the filter column (prevents ambiguous column errors when joining)
     */
    protected function invoiceFilterColumn(): string
    {
        $col = $this->invoice_filter ?: 'created_at';

        if (str_contains($col, '.')) {
            return $col;
        }

        return "invoices.$col";
    }

    /**
     * ✅ Apply all filters in one place (export + summary always match)
     */
    protected function applyFilters(Builder $q): Builder
    {
        $filterCol = $this->invoiceFilterColumn();

        // Date filter: range OR current month/year
        $q->when(
            filled($this->from) && filled($this->to),
            fn (Builder $qq) => $qq->whereBetween($filterCol, [
                Carbon::parse($this->from)->startOfDay(),
                Carbon::parse($this->to)->endOfDay(),
            ]),
            fn (Builder $qq) => $qq->whereMonth($filterCol, now()->month)
                                  ->whereYear($filterCol, now()->year)
        );

        // Search (GROUPED)
        $q->when(filled($this->search), function (Builder $qq) {
            $s = trim($this->search);

            $qq->where(function (Builder $w) use ($s) {
                $w->where('invoices.invoice_number', 'like', "%{$s}%")
                  ->orWhere('invoices.status', 'like', "%{$s}%")
                  ->orWhere('invoices.date', 'like', "%{$s}%")
                  ->orWhere('invoices.expiry', 'like', "%{$s}%")
                  ->orWhere('invoices.authorization', 'like', "%{$s}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('transporter', fn ($tq) => $tq->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('currency', fn ($cq) => $cq->where('name', 'like', "%{$s}%"));
            });
        });

        if (!blank($this->customer_id)) {
            $q->where('invoices.customer_id', $this->customer_id);
        }
        if (!blank($this->currency_id)) {
            $q->where('invoices.currency_id', $this->currency_id);
        }
        if (!blank($this->transporter_id)) {
            $q->where('invoices.transporter_id', $this->transporter_id);
        }

        // Tax filter
        if ($this->tax_status === 'taxed') {
            $q->whereNotNull('invoices.tax_amount')
              ->where('invoices.tax_amount', '!=', 0)
              ->where('invoices.tax_amount', '!=', '');
        } elseif ($this->tax_status === 'non-taxed') {
            $q->where(function ($qq) {
                $qq->whereNull('invoices.tax_amount')
                   ->orWhere('invoices.tax_amount', 0)
                   ->orWhere('invoices.tax_amount', '');
            });
        }

        return $q;
    }

    /**
     * ✅ Compute summary + owing recipients row
     *
     * Assumption (per your instruction):
     * - If invoice currency != company currency => use invoices.exchange_amount as BASE-CURRENCY owing figure
     * - Else use invoices.balance
     */
    protected function computeSummary(): void
    {
        $base = $this->applyFilters(Invoice::query());

        $this->summaryInvoicesCount = (int) (clone $base)->count();

        // Group totals per currency
        $rows = (clone $base)
            ->leftJoin('currencies', 'invoices.currency_id', '=', 'currencies.id')
            ->selectRaw("
                COALESCE(currencies.name, 'Unknown') as currency_name,
                COALESCE(currencies.symbol, '') as currency_symbol,
                COALESCE(SUM(invoices.total), 0) as revenue_sum,
                COALESCE(SUM(invoices.balance), 0) as due_sum
            ")
            ->groupBy('currency_name', 'currency_symbol')
            ->orderBy('currency_name')
            ->get();

        $this->summaryByCurrency = $rows->map(fn ($r) => [
            'name'    => $r->currency_name,
            'symbol'  => $r->currency_symbol,
            'revenue' => (float) $r->revenue_sum,
            'due'     => (float) $r->due_sum,
            'paid'    => (float) $r->revenue_sum - (float) $r->due_sum,
        ])->toArray();
    }
    public function query()
    {
        // ✅ compute summary once
        $this->computeSummary();

        $base = $this->applyFilters(
            Invoice::query()->with([
                'customer:id,name',
                'transporter:id,name',
                'currency',
                'user:id,name,surname',
                'invoice_items.product',
                'invoice_items.inventory',
                'invoice_items.trip',
                // if you have payments relation and want it for map:
                // 'payments',
                // 'invoice_payments',
            ])
        );

        return $base->orderByDesc($this->invoiceFilterColumn());
    }

    public function map($invoice): array
    {
        $symbol   = $invoice->currency ? $invoice->currency->symbol : "";
        $currency = $invoice->currency ? $invoice->currency->name : "";

        $subtotal   = number_format((float)($invoice->subtotal ?? 0), 2);
        $tax_amount = number_format((float)($invoice->tax_amount ?? 0), 2);
        $total      = number_format((float)($invoice->total ?? 0), 2);

        // Safer paid calc: total - balance (avoids payment-join complexities)
        $paidRaw = (float)($invoice->total ?? 0) - (float)($invoice->balance ?? 0);
        $payments = number_format($paidRaw, 2);

        $balance = number_format((float)($invoice->balance ?? 0), 2);

        $invoice_to = null;
        if ($invoice->customer) {
            $invoice_to = $invoice->customer->name;
        } elseif ($invoice->transporter) {
            $invoice_to = $invoice->transporter->name;
        }

        $narrations = [];
        foreach (($invoice->invoice_items ?? []) as $item) {
            $invoice_item = "";

            if ($item->product) {
                $parts = [];
                if (!blank($item->product->name ?? null)) $parts[] = $item->product->name;
                if (!blank($item->product->identification_number ?? null)) $parts[] = $item->product->identification_number;
                if (!blank($item->inventory->serial_number ?? null)) $parts[] = $item->inventory->serial_number;
                $invoice_item = implode(' ', $parts);
            } elseif ($item->trip) {
                $invoice_item = $item->trip->trip_number ?? '';
            }

            $narrations[] = trim($invoice_item . ' ' . ($item->description ?? '')) . ' ' . number_format((float)($item->subtotal_incl ?? 0), 2);
        }
        $narration = implode(', ', $narrations);

        $created_by = trim(($invoice->user->name ?? '') . ' ' . ($invoice->user->surname ?? ''));

        return [
            $invoice->invoice_number,
            $created_by,
            $invoice_to,
            $narration,
            $invoice->date,
            $invoice->expiry,
            $invoice->status,
            $currency . ' ' . $symbol,
            $subtotal,
            $tax_amount,
            $total,
            $payments,
            $balance,
        ];
    }

    public function headings(): array
    {
        return [
            'Invoice#',
            'CreatedBy',
            'InvoiceTo',
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

                // ✅ Table headings style
                $event->sheet->getStyle('A8:M8')->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ],
                ]);

                $sheet = $event->sheet->getDelegate();

                // Item(s) is column D in your SalesExport
                $sheet->getColumnDimension('D')->setWidth(60);   // try 30–55
                $sheet->getColumnDimension('B')->setWidth(20);   // try 30–55
                $sheet->getColumnDimension('C')->setWidth(20);   // try 30–55

                // Optional: wrap so it stays within that width (still horizontal control)
                $sheet->getStyle('D')->getAlignment()->setWrapText(true);
                $sheet->getStyle('B')->getAlignment()->setWrapText(true);
                $sheet->getStyle('C')->getAlignment()->setWrapText(true);
                // Summary starts at C2
                $titleRow = 2;
                $countRow = 3;
                $revRow   = 4;
                $paidRow  = 5;
                $dueRow   = 6;

                $startColIndex = 3; // C
                $pairStartCol  = 4; // D

                $k = count($this->summaryByCurrency);
                $lastColIndex = $k > 0 ? ($startColIndex + 2 * $k) : ($pairStartCol); // at least to D
                $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);

                // Title merged across the full summary width
                $sheet->mergeCells("C{$titleRow}:{$lastCol}{$titleRow}");
                $sheet->setCellValue("C{$titleRow}", "SUMMARY");

                // Invoices count (single value)
                $sheet->setCellValue("C{$countRow}", "Total Invoices Count");
                $sheet->setCellValue("D{$countRow}", $this->summaryInvoicesCount);

                // Labels
                $sheet->setCellValue("C{$revRow}",  "Total Revenue");
                $sheet->setCellValue("C{$paidRow}", "Total Paid");
                $sheet->setCellValue("C{$dueRow}",  "Total Due");

                // Write currency pairs horizontally: [CUR][AMOUNT] [CUR][AMOUNT] ...
                $colIndex = $pairStartCol;

                foreach ($this->summaryByCurrency as $c) {
                    $curCol = Coordinate::stringFromColumnIndex($colIndex);
                    $amtCol = Coordinate::stringFromColumnIndex($colIndex + 1);

                    // Revenue row
                    $sheet->setCellValue("{$curCol}{$revRow}", $c['name']);
                    $sheet->setCellValue("{$amtCol}{$revRow}", $c['revenue']);

                    // Paid row
                    $sheet->setCellValue("{$curCol}{$paidRow}", $c['name']);
                    $sheet->setCellValue("{$amtCol}{$paidRow}", $c['paid']);

                    // Due row
                    $sheet->setCellValue("{$curCol}{$dueRow}", $c['name']);
                    $sheet->setCellValue("{$amtCol}{$dueRow}", $c['due']);

                    // Number format for amounts
                    $sheet->getStyle("{$amtCol}{$revRow}:{$amtCol}{$dueRow}")
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

                    // Make currency codes bold
                    $sheet->getStyle("{$curCol}{$revRow}:{$curCol}{$dueRow}")
                        ->getFont()
                        ->setBold(true);

                    $colIndex += 2;
                }

                // Apply the same background + borders around the whole summary
                $summaryRange = "C{$titleRow}:{$lastCol}{$dueRow}";

                $sheet->getStyle($summaryRange)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FCE4D6'], // light grey
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

                // Title styling
                $sheet->getStyle("C{$titleRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Label column bold
                $sheet->getStyle("C{$countRow}:C{$dueRow}")->getFont()->setBold(true);
            },
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();

        $company = Auth::user()?->employee?->company;
               if ($company) {
            $drawing->setName($company->name);
            $drawing->setDescription($company->name . ' Logo');

            $logoPath = public_path('/images/uploads/' . ($company->logo ?? ''));
            if (!empty($company->logo) && file_exists($logoPath)) {
                $drawing->setPath($logoPath);
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
        return 'A8';
    }
}