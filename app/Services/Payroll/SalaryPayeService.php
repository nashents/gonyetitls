<?php

namespace App\Services\Payroll;

use App\Models\Allowance;
use App\Models\Deduction;
use App\Models\Salary;
use App\Models\SalaryItem;
use App\Models\TaxBracket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * PAYE + AIDS Levy on a salary template.
 *
 * PAYE is charged on gross less the salary's non-taxable allowances
 * (allowances.taxable = false). AIDS Levy is 3% of PAYE. Used by
 * Salaries Create/Edit and to re-run PAYE on every salary using an
 * allowance whose Taxable flag was changed.
 */
class SalaryPayeService
{
    public const AIDS_LEVY_RATE = 0.03;

    /**
     * An item's value in the same terms Salaries Create totals allowances:
     * foreign-currency rows use exchange_amount, everything else amount.
     */
    public static function itemValue(SalaryItem $item, ?int $companyCurrencyId): float
    {
        return $item->currency_id && $companyCurrencyId && $item->currency_id != $companyCurrencyId
            ? (float) $item->exchange_amount
            : (float) $item->amount;
    }

    public static function companyCurrencyId(?Salary $salary = null): ?int
    {
        return $salary?->employee?->company?->currency_id
            ?? Auth::user()?->employee?->company?->currency_id
            ?? Auth::user()?->company?->currency_id;
    }

    public function nonTaxableAllowances(int $salaryId, ?int $companyCurrencyId): float
    {
        return (float) SalaryItem::where('salary_id', $salaryId)
            ->whereHas('allowance', fn ($q) => $q->where('taxable', false))
            ->get()
            ->sum(fn ($item) => self::itemValue($item, $companyCurrencyId));
    }

    /**
     * PAYE for a taxable amount from the flat tax_brackets table.
     *
     * lower_band/upper_band are VARCHAR — cast them, or MySQL's implicit
     * cast against a float binding can pick the unbounded top bracket.
     */
    public function paye(float $taxable, $currencyId, $frequency): float
    {
        $bracket = TaxBracket::where('currency_id', $currencyId)
            ->where('frequency', $frequency)
            ->where(function ($q) use ($taxable) {
                $q->whereRaw('CAST(lower_band AS DECIMAL(18,2)) <= ?', [$taxable])->orWhereNull('lower_band');
            })
            ->where(function ($q) use ($taxable) {
                $q->whereRaw('CAST(upper_band AS DECIMAL(18,2)) >= ?', [$taxable])->orWhereNull('upper_band');
            })
            ->first();

        if (!$bracket || !is_numeric($bracket->percentage)) {
            return 0.0;
        }

        $paye = $taxable * ($bracket->percentage / 100) - ($bracket->rate ?? 0);

        return $paye > 0 ? $paye : 0.0;
    }

    /**
     * Create the PAYE (and AIDS Levy, if the salary has it ticked) items.
     * Returns the total added to the salary's deductions.
     */
    public function applyPaye(Salary $salary, float $taxable, $exchangeRate = null): float
    {
        $paye = $this->paye($taxable, $salary->currency_id, $salary->frequency);
        if ($paye <= 0) {
            return 0.0;
        }

        $this->createItem($salary->id, 'PAYE', $paye, $exchangeRate);
        $total = $paye;

        if ($salary->aids_levy) {
            $levy = $paye * self::AIDS_LEVY_RATE;
            $this->createItem($salary->id, 'AIDS Levy', $levy, $exchangeRate);
            $total += $levy;
        }

        return $total;
    }

    /**
     * Re-run PAYE/AIDS Levy on a saved salary from its current items, and
     * fix total_deductions/net by the difference. Gross is unchanged (the
     * Taxable flag only moves amounts in/out of the PAYE base).
     */
    public function recalculate(Salary $salary): void
    {
        DB::transaction(function () use ($salary) {
            $currencyId = self::companyCurrencyId($salary);
            $autoIds = Deduction::whereIn('name', ['PAYE', 'AIDS Levy'])->pluck('id');

            $old = SalaryItem::where('salary_id', $salary->id)->whereIn('deduction_id', $autoIds)->get();
            $oldTotal = $old->sum(fn ($item) => self::itemValue($item, $currencyId));
            $exchangeRate = $old->firstWhere('exchange_rate', '!=', null)?->exchange_rate;
            SalaryItem::whereIn('id', $old->pluck('id'))->delete();

            $newTotal = 0.0;
            if ($salary->paye) {
                $taxable = max(0, (float) $salary->gross - $this->nonTaxableAllowances($salary->id, $currencyId));
                $newTotal = $this->applyPaye($salary, $taxable, $exchangeRate);
            }

            $salary->total_deductions = (float) $salary->total_deductions - $oldTotal + $newTotal;
            $salary->net = (float) $salary->gross - $salary->total_deductions;
            $salary->save();
        });
    }

    /**
     * Recalculate every salary that carries this allowance. Returns how
     * many salaries were updated.
     */
    public function recalculateForAllowance(Allowance $allowance): int
    {
        $salaries = Salary::with('employee.company')
            ->whereHas('salary_items', fn ($q) => $q->where('allowance_id', $allowance->id))
            ->get();

        foreach ($salaries as $salary) {
            $this->recalculate($salary);
        }

        return $salaries->count();
    }

    private function createItem(int $salaryId, string $name, float $amount, $exchangeRate): void
    {
        $deduction = Deduction::firstOrCreate(['name' => $name], ['status' => 1]);

        SalaryItem::create([
            'salary_id'       => $salaryId,
            'deduction_id'    => $deduction->id,
            'amount'          => $amount,
            'exchange_rate'   => $exchangeRate,
            'exchange_amount' => $exchangeRate ? $exchangeRate * $amount : null,
        ]);
    }
}
