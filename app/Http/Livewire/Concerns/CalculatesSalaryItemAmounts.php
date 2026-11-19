<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Deduction;
use App\Models\Earning;

/**
 * Salary Create / Edit: fill an earning's / deduction's amount when it is
 * picked, and keep PERCENTAGE rows in step with the salary:
 *   - earning %   = % of basic
 *   - deduction % = % of basic or of gross (basic + earnings), per deduction
 * Fixed-amount rows are only pre-filled once (when picked), so a typed
 * amount is never overwritten.
 *
 * Uses the forms' existing arrays: selectedAllowance / allowance_amount
 * (earnings) and selectedDeduction / deduction_amount.
 */
trait CalculatesSalaryItemAmounts
{
    private function basicValue(): float
    {
        return is_numeric($this->basic) ? (float) $this->basic : 0.0;
    }

    /** Basic + the earnings currently on the form. */
    private function grossValue(): float
    {
        $earnings = 0.0;
        foreach ((array) $this->selectedAllowance as $key => $earningId) {
            if (!empty($earningId) && is_numeric($this->allowance_amount[$key] ?? null)) {
                $earnings += (float) $this->allowance_amount[$key];
            }
        }

        return $this->basicValue() + $earnings;
    }

    protected function fillEarningAmount($key): void
    {
        $earning = !empty($this->selectedAllowance[$key]) ? Earning::find($this->selectedAllowance[$key]) : null;
        $amount = $earning?->amountFor($this->basicValue());
        if ($amount !== null) {
            $this->allowance_amount[$key] = $amount;
        }
    }

    protected function fillDeductionAmount($key): void
    {
        $deduction = !empty($this->selectedDeduction[$key]) ? Deduction::find($this->selectedDeduction[$key]) : null;
        $amount = $deduction?->amountFor($this->basicValue(), $this->grossValue());
        if ($amount !== null) {
            $this->deduction_amount[$key] = $amount;
        }
    }

    /** Re-work every percentage row (earnings first - deductions may be a % of gross). */
    protected function recalculatePercentageRows(bool $earnings = true): void
    {
        if ($earnings) {
            $ids = array_filter((array) $this->selectedAllowance);
            $percent = Earning::whereIn('id', $ids)->where('calculate_by', 'percentage')->pluck('id')->all();
            foreach ((array) $this->selectedAllowance as $key => $id) {
                if (in_array($id, $percent)) {
                    $this->fillEarningAmount($key);
                }
            }
        }

        $ids = array_filter((array) $this->selectedDeduction);
        $percent = Deduction::whereIn('id', $ids)->where('calculate_by', 'percentage')
            ->where(fn ($q) => $q->whereNull('contribution_type')->orWhere('contribution_type', '!=', Deduction::CONTRIBUTION_SHARED))
            ->pluck('id')->all();
        foreach ((array) $this->selectedDeduction as $key => $id) {
            if (in_array($id, $percent)) {
                $this->fillDeductionAmount($key);
            }
        }
    }

    public function updatedBasic(): void
    {
        $this->recalculatePercentageRows();
    }

    /** An earning amount changed: % of gross deductions follow (earnings themselves are left as typed). */
    public function updatedAllowanceAmount(): void
    {
        $this->recalculatePercentageRows(false);
    }
}
