<?php

namespace App\Http\Livewire\Freight\Reports\UnbilledCosts;

use App\Services\Freight\UnbilledCostsAgingCalculator;
use App\Services\ReportFormatter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public $as_of_date;
    public $details;
    public $summary = 'summary';

    public $default_currency;
    public $default_currency_id;

    public $vendor_rows = [];
    public $grand_totals = ['buckets' => [], 'total' => 0];

    public function mount()
    {
        $this->as_of_date = Carbon::today()->format('Y-m-d');
    }

    public function set_report($value)
    {
        $this->summary = null;
        $this->details = null;

        if ($value == 'details') {
            $this->details = 'details';
        } elseif ($value == 'summary') {
            $this->summary = 'summary';
        }
    }

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

        if (isset($this->as_of_date)) {
            $calculator = new UnbilledCostsAgingCalculator($this->as_of_date);
            [$this->vendor_rows, $this->grand_totals] = $calculator->byVendor();
        }

        return view('livewire.freight.reports.unbilled-costs.index');
    }
}
