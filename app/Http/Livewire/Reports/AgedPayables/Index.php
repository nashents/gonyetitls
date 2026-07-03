<?php

namespace App\Http\Livewire\Reports\AgedPayables;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\ReportFormatter;
use App\Services\AgedPayablesCalculator;

class Index extends Component
{
    public $as_of_date;
    public $details;
    public $summary = "summary";

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

        if (isset($this->as_of_date)) {
            $calculator = new AgedPayablesCalculator($this->as_of_date, $this->default_currency_id);
            [$this->vendor_rows, $this->grand_totals] = $calculator->byVendor();
        }

        return view('livewire.reports.aged-payables.index');
    }
}
