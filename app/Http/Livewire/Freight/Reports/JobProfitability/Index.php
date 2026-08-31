<?php

namespace App\Http\Livewire\Freight\Reports\JobProfitability;

use App\Models\Customer;
use App\Models\FreightServiceType;
use App\Models\User;
use App\Services\Freight\JobProfitabilityCalculator;
use App\Services\ReportFormatter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public $from, $to;
    public $customer_id, $salesperson_id, $freight_service_type_id, $primary_transport_mode, $status;
    public $details;
    public $summary = 'summary';

    public $default_currency;
    public $default_currency_id;

    public $rows = [];
    public $grand_totals = ['jobCount' => 0, 'revenue' => 0.0, 'cost' => 0.0, 'margin' => 0.0, 'marginPct' => 0.0];

    public function mount()
    {
        $this->to = Carbon::today()->format('Y-m-d');
        $this->from = Carbon::today()->firstOfYear()->format('Y-m-d');
    }

    public function getCustomersProperty()
    {
        return Customer::orderBy('name', 'asc')->get(['id', 'name']);
    }

    public function getSalespersonsProperty()
    {
        return User::orderBy('name', 'asc')->get(['id', 'name']);
    }

    public function getFreightServiceTypesProperty()
    {
        return FreightServiceType::orderBy('name', 'asc')->get(['id', 'name']);
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

        if (isset($this->from) && isset($this->to)) {
            $calculator = new JobProfitabilityCalculator(
                $this->from,
                $this->to,
                $this->customer_id ?: null,
                $this->salesperson_id ?: null,
                $this->freight_service_type_id ?: null,
                $this->primary_transport_mode ?: null,
                $this->status ?: null,
            );

            [$this->rows, $this->grand_totals] = $this->details
                ? $calculator->details()
                : $calculator->summaryByCustomer();
        }

        return view('livewire.freight.reports.job-profitability.index');
    }
}
