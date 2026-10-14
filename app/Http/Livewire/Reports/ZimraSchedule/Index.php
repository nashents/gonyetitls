<?php

namespace App\Http\Livewire\Reports\ZimraSchedule;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;
use App\Services\ReportFormatter;
use App\Services\ZimraScheduleCalculator;

class Index extends Component
{
    public $from;
    public $to;
    public $selectedCurrency = '';

    protected $queryString = ['from', 'to', 'selectedCurrency' => ['except' => '']];

    public function mount()
    {
        $this->from = $this->from ?: request()->query('from', Carbon::now()->subMonthNoOverflow()->startOfMonth()->format('Y-m-d'));
        $this->to = $this->to ?: request()->query('to', Carbon::now()->endOfMonth()->format('Y-m-d'));
    }

    /** Quick period presets (ZIMRA VAT periods are usually two months). */
    public function setPeriod($period)
    {
        $now = Carbon::now();

        [$from, $to] = match ($period) {
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            'last_two_months' => [$now->copy()->subMonthsNoOverflow(2)->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [Carbon::parse($this->from), Carbon::parse($this->to)],
        };

        $this->from = $from->format('Y-m-d');
        $this->to = $to->format('Y-m-d');
    }

    public function generateStatement()
    {
        //
    }

    public function fmt($value): string
    {
        return ReportFormatter::money($value);
    }

    public function exportParams(): array
    {
        return array_filter([
            'from' => $this->from,
            'to' => $this->to,
            'currency_id' => $this->selectedCurrency,
        ]);
    }

    public function render()
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        $groups = [];
        if ($this->from && $this->to) {
            $groups = (new ZimraScheduleCalculator(
                $this->from,
                $this->to,
                $this->selectedCurrency ?: null,
                $company?->currency_id
            ))->build();
        }

        return view('livewire.reports.zimra-schedule.index', [
            'company' => $company,
            'groups' => $groups,
            'currencies' => Currency::orderBy('name')->get(),
            'periodLabel' => ($this->from && $this->to) ? ZimraScheduleCalculator::periodLabel($this->from, $this->to) : '',
        ]);
    }
}
