<?php

namespace App\Services\Payroll;

use App\Models\Earning;
use App\Models\Deduction;
use App\Models\Salary;
use App\Models\SalaryItem;
use App\Models\TaxBracket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * PAYE + AIDS Levy on a salary template.
 *
 * PAYE is charged on gross less the salary's non-taxable earnings
 * (earnings.taxable = false). AIDS Levy is 3% of PAYE. Used by
 * Salaries Create/Edit/Import and to re-run PAYE on every salary using an
 * earning whose Taxable flag was changed. (Payroll uses Earnings; Allowances
 * are for driver trip expenses only.)
 */
class SalaryPayeService
{
    public const AIDS_LEVY_RATE = 0.03;

    /**
     * An item's value in the same terms Salaries Create totals earnings:
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

    public function nonTaxableEarnings(int $salaryId, ?int $companyCurrencyId): float
    {
        return (float) SalaryItem::where('salary_id', $salaryId)
            ->whereHas('earning', fn ($q) => $q->where('taxable', false))
            ->get()
            ->sum(fn ($item) => self::itemValue($item, $companyCurrencyId));
    }

    /** A line's company share in the same terms as itemValue() (foreign currency converted). */
    public static function employerValue(SalaryItem $item, ?int $companyCurrencyId): float
    {
        $employer = (float) $item->employer_amount;
        if ($employer <= 0) {
            return 0.0;
        }

        return $item->currency_id && $companyCurrencyId && $item->currency_id != $companyCurrencyId && $item->exchange_rate
            ? $employer * (float) $item->exchange_rate
            : $employer;
    }

    /**
     * Company-paid shares of shared-premium deductions (Medical Aid ...) that
     * are a taxable benefit: added to the PAYE base, not to gross / net.
     */
    public function taxableBenefits(int $salaryId, ?int $companyCurrencyId): float
    {
        return (float) SalaryItem::where('salary_id', $salaryId)
            ->whereHas('deduction', fn ($q) => $q->where('contribution_type', Deduction::CONTRIBUTION_SHARED)->where('employer_share_taxable', true))
            ->get()
            ->sum(fn ($item) => self::employerValue($item, $companyCurrencyId));
    }

    /**
     * PAYE credit: each deduction's paye_credit_percentage of the EMPLOYEE's
     * share (e.g. 50% of medical aid contributions), taken off PAYE.
     */
    public function payeCredit(int $salaryId, ?int $companyCurrencyId): float
    {
        return (float) SalaryItem::where('salary_id', $salaryId)
            ->whereHas('deduction', fn ($q) => $q->where('paye_credit_percentage', '>', 0))
            ->with('deduction:id,paye_credit_percentage')
            ->get()
            ->sum(fn ($item) => self::itemValue($item, $companyCurrencyId) * (float) $item->deduction->paye_credit_percentage / 100);
    }

    /**
     * PAYE (+ AIDS Levy) for a salary from its saved items - the one place
     * Create / Edit / Import / recalculate work it out:
     *   taxable = gross - non-taxable earnings + taxable company benefits
     *   PAYE    = tax table on taxable - PAYE credits (not below 0)
     *   AIDS Levy = 3% of PAYE after credits
     * Returns the total added to the salary's deductions.
     */
    public function applyPayeFor(Salary $salary, float $gross, ?int $companyCurrencyId, $exchangeRate = null): float
    {
        $taxable = max(0, $gross
            - $this->nonTaxableEarnings($salary->id, $companyCurrencyId)
            + $this->taxableBenefits($salary->id, $companyCurrencyId));

        return $this->applyPaye($salary, $taxable, $exchangeRate, $this->payeCredit($salary->id, $companyCurrencyId));
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
    public function applyPaye(Salary $salary, float $taxable, $exchangeRate = null, float $credit = 0.0): float
    {
        $paye = max(0.0, $this->paye($taxable, $salary->currency_id, $salary->frequency) - $credit);
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
                $newTotal = $this->applyPayeFor($salary, (float) $salary->gross, $currencyId, $exchangeRate);
            }

            $salary->total_deductions = (float) $salary->total_deductions - $oldTotal + $newTotal;
            $salary->net = (float) $salary->gross - $salary->total_deductions;
            $salary->save();
        });
    }

    /**
     * Recalculate every salary that carries this earning. Returns how
     * many salaries were updated.
     */
    public function recalculateForEarning(Earning $earning): int
    {
        $salaries = Salary::with('employee.company')
            ->whereHas('salary_items', fn ($q) => $q->where('earning_id', $earning->id))
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
