<?php

namespace App\Http\Livewire\Reports\SalesTax;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\ReportFormatter;
use App\Services\SalesTaxCalculator;

class Index extends Component
{
    const ACCRUAL = 'Accrual (Paid & Unpaid)';
    const CASH_BASIS = 'Cash Basis';

    public $from;
    public $to;
    public $selectedType = self::ACCRUAL;
    public $details;
    public $summary = "summary";

    public $default_currency;
    public $default_currency_id;

    public $output_tax_rows = [];
    public $total_taxable_sales = 0;
    public $total_tax_collected = 0;

    public $input_tax_rows = [];
    public $total_taxable_purchases = 0;
    public $total_tax_paid = 0;

    public $net_tax_payable = 0;

    public function mount()
    {
        $this->to = Carbon::today()->format('Y-m-d');
        $this->from = Carbon::now()->firstOfYear()->format('Y-m-d');
    }

    public function set_report($value)
    {
        $this->summary = null;
        $this->details = null;

        if ($value == "details") {
            $this->details = 'details';
        } elseif ($value == "summary") {
            $this->summary = 'summary';
        }
    }

    /**
     * Bound to the filter form's wire:submit so pressing Enter doesn't
     * error out looking for a missing action; the bound properties
     * already trigger a recalculation on change.
     */
    public function generateStatement()
    {
        //
    }

    public function viewMode(): string
    {
        return $this->details ? 'details' : 'summary';
    }

    public function fmt($value): string
    {
        return ReportFormatter::money($value);
    }

    public function render()
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;
        $this->default_currency = $company?->currency;
        $this->default_currency_id = $company?->currency_id;

        if (isset($this->from) && isset($this->to)) {
            $calculator = new SalesTaxCalculator(
                $this->from,
                $this->to,
                $this->selectedType === self::CASH_BASIS,
                $this->default_currency_id
            );

            [$this->total_taxable_sales, $this->total_tax_collected, $outputByTax] = $calculator->outputTax();
            [$this->total_taxable_purchases, $this->total_tax_paid, $inputByTax] = $calculator->inputTax();

            $labels = $calculator->taxLabels(array_unique(array_merge(array_keys($outputByTax), array_keys($inputByTax))));

            $this->output_tax_rows = $this->toRows($outputByTax, $labels);
            $this->input_tax_rows = $this->toRows($inputByTax, $labels);

            $this->net_tax_payable = $this->total_tax_collected - $this->total_tax_paid;
        }

        return view('livewire.reports.sales-tax.index');
    }

    private function toRows(array $byTax, array $labels): array
    {
        $rows = [];

        foreach ($byTax as $taxId => $amounts) {
            $rows[] = [
                'label' => $labels[$taxId] ?? 'No Tax',
                'taxable' => $amounts['taxable'],
                'tax' => $amounts['tax'],
            ];
        }

        return $rows;
    }
}
