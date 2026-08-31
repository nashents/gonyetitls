<?php

namespace App\Http\Livewire\Freight\Reports\CustomsTurnaround;

use App\Models\User;
use App\Services\Freight\CustomsTurnaroundCalculator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public $from, $to, $clearing_officer_id;
    public $details;
    public $summary = 'summary';

    public $default_currency;

    public $rows = [];
    public $overall = ['count' => 0, 'avgDays' => 0.0, 'medianDays' => 0.0];

    public function mount()
    {
        $this->to = Carbon::today()->format('Y-m-d');
        $this->from = Carbon::today()->firstOfYear()->format('Y-m-d');
    }

    public function getClearingOfficersProperty()
    {
        return User::orderBy('name', 'asc')->get(['id', 'name']);
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

    public function render()
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;
        $this->default_currency = $company?->currency;

        if (isset($this->from) && isset($this->to)) {
            $calculator = new CustomsTurnaroundCalculator($this->from, $this->to, $this->clearing_officer_id ?: null);

            if ($this->details) {
                $this->rows = $calculator->details();
                [, $this->overall] = $calculator->byClearingOfficer();
            } else {
                [$this->rows, $this->overall] = $calculator->byClearingOfficer();
            }
        }

        return view('livewire.freight.reports.customs-turnaround.index');
    }
}
