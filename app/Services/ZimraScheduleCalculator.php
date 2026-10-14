<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Invoice;
use App\Models\Currency;
use App\Models\CreditNote;

/**
 * ZIMRA Sales & Purchases Schedule: every approved invoice, credit note
 * and bill in the period, listed per currency and split into "with VAT"
 * and "without VAT" (zero-rated / exempt / no tax) schedules, mirroring
 * the VAT return workbook the accountants submit.
 *
 * Amounts are reported in each document's own currency - the schedule is
 * submitted per currency, never converted.
 *
 * Header totals are unreliable on older records (bills.subtotal and
 * bills.tax_amount are often null, and some invoices carry VAT only on the
 * header), so amounts come from the line items, falling back to the header
 * where the lines carry nothing.
 */
class ZimraScheduleCalculator
{
    const SALES_VAT = 'sales_vat';
    const SALES_NO_VAT = 'sales_no_vat';
    const CREDIT_NOTES = 'credit_notes';
    const PURCHASES_VAT = 'purchases_vat';
    const PURCHASES_NO_VAT = 'purchases_no_vat';

    /** Section order and titles, as laid out on the ZIMRA workbook. */
    const SECTIONS = [
        self::SALES_VAT => 'SALES INVOICES (WITH VAT)',
        self::SALES_NO_VAT => 'SALES INVOICES (WITHOUT VAT / ZERO-RATED & EXEMPT)',
        self::CREDIT_NOTES => 'CREDIT NOTES',
        self::PURCHASES_VAT => 'VAT PURCHASES SCHEDULE',
        self::PURCHASES_NO_VAT => 'WITHOUT VAT PURCHASES SCHEDULE',
    ];

    /** Anything below half a cent of VAT is treated as no VAT. */
    const VAT_EPSILON = 0.005;

    public function __construct(
        private string $from,
        private string $to,
        private $currencyId = null,
        private $defaultCurrencyId = null
    ) {
    }

    /**
     * @return array<int, array{
     *     currency: ?Currency,
     *     sections: array<string, array{title: string, rows: array, totals: array{sub: float, vat: float, total: float}}>,
     *     summary: array{output_vat: float, credit_vat: float, input_vat: float, net_vat: float}
     * }>
     */
    public function build(): array
    {
        // Zero-value documents (e.g. cancelled-out fuel bills) add nothing to the return.
        $rows = array_filter(
            array_merge($this->invoiceRows(), $this->creditNoteRows(), $this->billRows()),
            fn ($row) => abs($row['sub']) >= self::VAT_EPSILON || abs($row['vat']) >= self::VAT_EPSILON
        );

        $byCurrency = [];
        foreach ($rows as $row) {
            $byCurrency[$row['currency_id'] ?? 0][$row['section']][] = $row;
        }

        $currencies = Currency::whereIn('id', array_filter(array_keys($byCurrency)))->get()->keyBy('id');

        // Base currency first, then the rest alphabetically.
        uksort($byCurrency, function ($a, $b) use ($currencies) {
            $aBase = (int) $a === (int) $this->defaultCurrencyId;
            $bBase = (int) $b === (int) $this->defaultCurrencyId;
            if ($aBase !== $bBase) {
                return $aBase ? -1 : 1;
            }

            return strcmp($currencies[$a]->name ?? '', $currencies[$b]->name ?? '');
        });

        $result = [];
        foreach ($byCurrency as $currencyId => $sectionRows) {
            $sections = [];
            foreach (self::SECTIONS as $key => $title) {
                $list = $sectionRows[$key] ?? [];
                usort($list, fn ($a, $b) => [$a['date'], $a['number']] <=> [$b['date'], $b['number']]);

                $sections[$key] = [
                    'title' => $title,
                    'rows' => $list,
                    'totals' => [
                        'sub' => array_sum(array_column($list, 'sub')),
                        'vat' => array_sum(array_column($list, 'vat')),
                        'total' => array_sum(array_column($list, 'total')),
                    ],
                ];
            }

            $outputVat = $sections[self::SALES_VAT]['totals']['vat'] + $sections[self::SALES_NO_VAT]['totals']['vat'];
            $creditVat = $sections[self::CREDIT_NOTES]['totals']['vat'];
            $inputVat = $sections[self::PURCHASES_VAT]['totals']['vat'] + $sections[self::PURCHASES_NO_VAT]['totals']['vat'];

            $result[] = [
                'currency' => $currencies[$currencyId] ?? null,
                'sections' => $sections,
                'summary' => [
                    'output_vat' => $outputVat,
                    'credit_vat' => $creditVat,
                    'input_vat' => $inputVat,
                    'net_vat' => $outputVat - $creditVat - $inputVat,
                ],
            ];
        }

        return $result;
    }

    /**
     * Period heading as the workbook writes it: "JUNE 2026 & JULY 2026" for
     * whole months, otherwise the exact dates.
     */
    public static function periodLabel(string $from, string $to): string
    {
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay();

        if ($start->isSameDay($start->copy()->startOfMonth()) && $end->isSameDay($end->copy()->endOfMonth()) && $start <= $end) {
            $months = [];
            for ($m = $start->copy(); $m <= $end; $m->addMonthNoOverflow()) {
                $months[] = strtoupper($m->format('F Y'));
            }

            if (count($months) <= 3) {
                return implode(' & ', $months);
            }

            return reset($months) . ' - ' . end($months);
        }

        return $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y');
    }

    private function invoiceRows(): array
    {
        $invoices = Invoice::query()
            ->where('authorization', 'approved')
            ->whereDate('date', '>=', $this->from)
            ->whereDate('date', '<=', $this->to)
            ->when($this->currencyId, fn ($q) => $q->where('currency_id', $this->currencyId))
            ->with(['customer:id,name,vat_number,tin_number', 'invoice_items:id,invoice_id,subtotal,tax_amount'])
            ->get(['id', 'customer_id', 'currency_id', 'invoice_number', 'date', 'subtotal', 'tax_amount', 'total']);

        $rows = [];
        foreach ($invoices as $invoice) {
            [$sub, $vat] = $this->amounts($invoice->invoice_items, $invoice);

            $rows[] = $this->row(
                $this->hasVat($vat) ? self::SALES_VAT : self::SALES_NO_VAT,
                $invoice->currency_id,
                $invoice->date,
                $invoice->customer?->name,
                $invoice->customer?->vat_tin,
                $invoice->invoice_number,
                null,
                $sub,
                $vat,
                route('invoices.show', $invoice->id)
            );
        }

        return $rows;
    }

    private function creditNoteRows(): array
    {
        $notes = CreditNote::query()
            ->where('authorization', 'approved')
            ->whereDate('date', '>=', $this->from)
            ->whereDate('date', '<=', $this->to)
            ->when($this->currencyId, fn ($q) => $q->where('currency_id', $this->currencyId))
            ->with('customer:id,name,vat_number,tin_number')
            ->get(['id', 'customer_id', 'currency_id', 'credit_note_number', 'date', 'subtotal', 'tax_amount', 'total']);

        $rows = [];
        foreach ($notes as $note) {
            [$sub, $vat] = $this->amounts(collect(), $note);

            $rows[] = $this->row(
                self::CREDIT_NOTES,
                $note->currency_id,
                $note->date,
                $note->customer?->name,
                $note->customer?->vat_tin,
                $note->credit_note_number,
                null,
                $sub,
                $vat,
                route('credit_notes.show', $note->id)
            );
        }

        return $rows;
    }

    private function billRows(): array
    {
        $bills = Bill::query()
            ->where('authorization', 'approved')
            ->whereDate('bill_date', '>=', $this->from)
            ->whereDate('bill_date', '<=', $this->to)
            ->when($this->currencyId, fn ($q) => $q->where('currency_id', $this->currencyId))
            ->with([
                'vendor:id,name,vat_number,tin_number',
                'transporter:id,name',
                'driver:id,employee_id',
                'driver.employee:id,name,surname',
                'bill_expenses:id,bill_id,expense_id,product_id,account_id,description,subtotal,tax_amount',
                'bill_expenses.expense:id,name',
                'bill_expenses.product:id,name',
                'bill_expenses.account:id,name',
            ])
            ->get(['id', 'vendor_id', 'transporter_id', 'driver_id', 'category', 'currency_id', 'bill_number', 'invoice_number', 'bill_date', 'notes', 'subtotal', 'tax_amount', 'total']);

        $rows = [];
        foreach ($bills as $bill) {
            [$sub, $vat] = $this->amounts($bill->bill_expenses, $bill);

            $rows[] = $this->row(
                $this->hasVat($vat) ? self::PURCHASES_VAT : self::PURCHASES_NO_VAT,
                $bill->currency_id,
                $bill->bill_date,
                $this->billParty($bill),
                $bill->vendor?->vat_tin,
                $bill->invoice_number,
                $this->billDescription($bill),
                $sub,
                $vat,
                route('bills.show', $bill->id),
                $bill->bill_number
            );
        }

        return $rows;
    }

    /**
     * [subtotal excl. VAT, VAT] for a document. Line VAT wins when the lines
     * carry any; otherwise the header VAT is used (header-level VAT on some
     * invoices). The subtotal follows the lines whenever there are lines.
     */
    private function amounts($lines, $header): array
    {
        $lineSub = (float) $lines->sum(fn ($l) => (float) $l->subtotal);
        $lineVat = (float) $lines->sum(fn ($l) => (float) $l->tax_amount);

        $vat = abs($lineVat) >= self::VAT_EPSILON ? $lineVat : (float) $header->tax_amount;

        if ($lines->isNotEmpty()) {
            $sub = $lineSub;
        } elseif (!is_null($header->subtotal) && $header->subtotal !== '') {
            $sub = (float) $header->subtotal;
        } else {
            $sub = (float) $header->total - $vat;
        }

        return [round($sub, 2), round($vat, 2)];
    }

    private function hasVat(float $vat): bool
    {
        return abs($vat) >= self::VAT_EPSILON;
    }

    /** Vendor, else transporter, else the driver a trip expense was paid to. */
    private function billParty(Bill $bill): string
    {
        if ($bill->vendor) {
            return (string) $bill->vendor->name;
        }
        if ($bill->transporter) {
            return (string) $bill->transporter->name;
        }
        if ($bill->driver?->employee) {
            return trim($bill->driver->employee->name . ' ' . $bill->driver->employee->surname) . ' (Driver)';
        }

        return (string) $bill->category;
    }

    private function billDescription(Bill $bill): string
    {
        $parts = $bill->bill_expenses
            ->map(fn ($line) => trim((string) ($line->description ?: ($line->expense?->name ?? $line->product?->name ?? $line->account?->name))))
            ->filter()
            ->unique()
            ->values();

        $text = $parts->isNotEmpty() ? $parts->implode(', ') : trim((string) $bill->notes);

        return mb_strimwidth($text, 0, 120, '...');
    }

    private function row(string $section, $currencyId, $date, $party, $vatTin, $number, $description, float $sub, float $vat, string $url, $reference = null): array
    {
        return [
            'section' => $section,
            'currency_id' => $currencyId,
            'date' => $date ? substr((string) $date, 0, 10) : '',
            'party' => (string) $party,
            'vat_tin' => (string) $vatTin,
            'number' => (string) $number,
            'reference' => (string) $reference,
            'description' => (string) $description,
            'sub' => $sub,
            'vat' => $vat,
            'total' => round($sub + $vat, 2),
            'url' => $url,
        ];
    }
}
