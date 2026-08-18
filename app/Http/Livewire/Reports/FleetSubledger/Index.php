<?php

namespace App\Http\Livewire\Reports\FleetSubledger;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\FleetSubledgerCalculator;

class Index extends Component
{
    public string $dimension = 'horse';
    public string $date_from = '';
    public string $date_to = '';
    public string $search = '';

    public function mount(): void
    {
        $this->date_from = now()->startOfYear()->toDateString();
        $this->date_to   = now()->toDateString();
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

    public function setDimension(string $dimension): void
    {
        if (array_key_exists($dimension, FleetSubledgerCalculator::DIMENSIONS)) {
            $this->dimension = $dimension;
        }
    }

    public function render()
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        $rows = [];
        $totals = ['debit' => 0.0, 'credit' => 0.0];

        if ($company) {
            $calculator = new FleetSubledgerCalculator(
                $company->id,
                $this->date_from,
                $this->date_to,
                $this->dimension,
                $this->search
            );

            [$rows, $totals] = $calculator->byUnit();
        }

        return view('livewire.reports.fleet-subledger.index', [
            'rows'       => $rows,
            'totals'     => $totals,
            'dimensions' => FleetSubledgerCalculator::DIMENSIONS,
        ]);
    }
}
