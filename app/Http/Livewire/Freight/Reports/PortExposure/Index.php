<?php

namespace App\Http\Livewire\Freight\Reports\PortExposure;

use App\Models\Vendor;
use App\Services\Freight\PortExposureCalculator;
use App\Services\ReportFormatter;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public $shipping_line_vendor_id;
    public $details;
    public $summary = 'summary';

    public $default_currency;
    public $default_currency_id;

    public $vendor_rows = [];
    public $grand_totals = ['buckets' => [], 'total' => 0, 'actual_total' => 0];
    public $status_breakdown = [];

    public function getShippingLinesProperty()
    {
        return Vendor::orderBy('name', 'asc')->get(['id', 'name']);
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

        $calculator = new PortExposureCalculator($this->shipping_line_vendor_id ?: null);
        [$this->vendor_rows, $this->grand_totals] = $calculator->byShippingLine();
        $this->status_breakdown = $calculator->statusBreakdown();

        return view('livewire.freight.reports.port-exposure.index');
    }
}
