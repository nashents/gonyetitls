<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\EmployeeNecAssignment;
use App\Models\EmployeePensionEnrollment;
use App\Models\PayrollCompanyConfig;
use App\Models\StatutoryDeductionType;

/**
 * StatutoryCalculator
 *
 * Config-driven PAYE / NSSA / NEC / Pension calculations, resolved from the
 * database for a given company and effective date — never hardcoded.
 *
 * Single source of truth for this math: used by both the legacy per-employee
 * Salary creation flow and any future per-run recalculation, so the two
 * never compute statutory deductions differently.
 */
class StatutoryCalculator
{
    private int $companyId;
    private string $country;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
        $this->country   = PayrollCompanyConfig::where('company_id', $companyId)
            ->where('active', true)
            ->value('country') ?? 'ZW';
    }

    // ════════════════════════════════════════════════════════════════════════
    // PAYE (bracket-based, config-driven)
    // ════════════════════════════════════════════════════════════════════════

    public function computePaye(float $taxableMonthly, string $date, int $periodsPerYear = 12): array
    {
        $type = $this->getStatutoryType('PAYE');
        if (!$type) return ['paye' => 0.0, 'aids_levy' => 0.0];

        $brackets = $type->taxBrackets()
            ->where('effective_from', '<=', $date)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date))
            ->orderBy('lower_band')
            ->get();

        if ($brackets->isEmpty()) return ['paye' => 0.0, 'aids_levy' => 0.0];

        $annualIncome = $taxableMonthly * $periodsPerYear;
        $annualPaye   = 0.0;
        $aidsLevy     = 0.0;

        foreach ($brackets as $bracket) {
            $lower = (float) $bracket->lower_band;
            $upper = $bracket->upper_band !== null ? (float) $bracket->upper_band : PHP_FLOAT_MAX;
            $rate  = (float) $bracket->rate_percentage / 100;

            if ($annualIncome <= $lower) break;

            $taxableInBand = min($annualIncome, $upper) - $lower;
            $taxInBand     = $taxableInBand * $rate;
            $annualPaye   += $taxInBand;

            if ((float) $bracket->aids_levy_percentage > 0) {
                $aidsLevy += $taxInBand * ((float) $bracket->aids_levy_percentage / 100);
            }
        }

        return [
            'paye'      => round($annualPaye / $periodsPerYear, 4),
            'aids_levy' => round($aidsLevy   / $periodsPerYear, 4),
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // NSSA (ceiling-based, config-driven)
    // ════════════════════════════════════════════════════════════════════════

    public function computeNssa(float $earnings, string $date): array
    {
        $type = $this->getStatutoryType('NSSA');
        if (!$type) return ['employee_amount' => 0.0, 'employer_amount' => 0.0, 'is_pre_tax' => false];

        $rate = $type->rateOn($date);
        if (!$rate) return ['employee_amount' => 0.0, 'employer_amount' => 0.0, 'is_pre_tax' => false];

        $ceiling         = $rate->earnings_ceiling ?? PHP_FLOAT_MAX;
        $insuredEarnings = min($earnings, (float) $ceiling);

        $employeeAmount = $this->applyRateCaps(
            $insuredEarnings * ((float) $rate->employee_percentage / 100),
            (float) $rate->minimum_employee_contribution,
            (float) $rate->maximum_employee_contribution
        );
        $employerAmount = $this->applyRateCaps(
            $insuredEarnings * ((float) $rate->employer_percentage / 100),
            (float) $rate->minimum_employer_contribution,
            (float) $rate->maximum_employer_contribution
        );

        return [
            'employee_amount' => round($employeeAmount, 4),
            'employer_amount' => round($employerAmount, 4),
            'is_pre_tax'      => $type->is_pre_tax,
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // NEC (category assigned to employee)
    // ════════════════════════════════════════════════════════════════════════

    public function computeNec(Employee $employee, float $earnings, string $date): array
    {
        $empty = ['employee_amount' => 0.0, 'employer_amount' => 0.0, 'nec_category_id' => null];

        $assignment = EmployeeNecAssignment::where('employee_id', $employee->id)
            ->where('effective_from', '<=', $date)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date))
            ->with('necCategory')
            ->latest('effective_from')
            ->first();

        if (!$assignment || !$assignment->necCategory) return $empty;

        $category = $assignment->necCategory;
        $rate     = $category->rateOn($date);
        if (!$rate) return $empty;

        $ceiling = $rate->earnings_ceiling ?? PHP_FLOAT_MAX;
        $insured = min($earnings, (float) $ceiling);

        $empAmount = $rate->employee_percentage > 0
            ? $insured * ((float) $rate->employee_percentage / 100)
            : (float) $rate->employee_fixed_amount;

        $emplrAmount = $rate->employer_percentage > 0
            ? $insured * ((float) $rate->employer_percentage / 100)
            : (float) $rate->employer_fixed_amount;

        return [
            'employee_amount' => round($empAmount, 4),
            'employer_amount' => round($emplrAmount, 4),
            'nec_category_id' => $category->id,
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // PENSION
    // ════════════════════════════════════════════════════════════════════════

    public function computePension(Employee $employee, float $earnings, string $date): array
    {
        $empty = ['employee_amount' => 0.0, 'employer_amount' => 0.0, 'pension_scheme_id' => null, 'is_pre_tax' => false];

        $enrollment = EmployeePensionEnrollment::where('employee_id', $employee->id)
            ->where('effective_from', '<=', $date)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date))
            ->with('pensionScheme')
            ->latest('effective_from')
            ->first();

        if (!$enrollment || !$enrollment->pensionScheme) return $empty;

        $scheme = $enrollment->pensionScheme;
        $rate   = $scheme->rateOn($date);
        if (!$rate) return $empty;

        $ceiling = $rate->earnings_ceiling ?? PHP_FLOAT_MAX;
        $insured = min($earnings, (float) $ceiling);

        $empAmount = $rate->employee_percentage > 0
            ? $insured * ((float) $rate->employee_percentage / 100)
            : (float) $rate->employee_fixed_amount;

        $voluntary = (float) $enrollment->voluntary_additional_percentage > 0
            ? $insured * ((float) $enrollment->voluntary_additional_percentage / 100)
            : (float) $enrollment->voluntary_additional_fixed_amount;

        $empAmount += $voluntary;

        $emplrAmount = $rate->employer_percentage > 0
            ? $insured * ((float) $rate->employer_percentage / 100)
            : (float) $rate->employer_fixed_amount;

        if ($rate->employer_match_percentage > 0) {
            $matchable   = $insured * ((float) $rate->employer_match_percentage / 100);
            $matchCap    = $rate->employer_match_cap ?? PHP_FLOAT_MAX;
            $matchAmount = min($empAmount, $matchable, (float) $matchCap);
            $emplrAmount += $matchAmount;
        }

        $empAmount   = $this->applyRateCaps($empAmount, (float) $rate->minimum_employee_contribution, (float) $rate->maximum_employee_contribution);
        $emplrAmount = $this->applyRateCaps($emplrAmount, (float) $rate->minimum_employer_contribution, (float) $rate->maximum_employer_contribution);

        $pensionStatType = $this->getStatutoryType('PENSION');

        return [
            'employee_amount'   => round($empAmount, 4),
            'employer_amount'   => round($emplrAmount, 4),
            'pension_scheme_id' => $scheme->id,
            'is_pre_tax'        => $pensionStatType?->is_pre_tax ?? false,
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════════════════════

    private function getStatutoryType(string $code): ?StatutoryDeductionType
    {
        return StatutoryDeductionType::where('code', $code)
            ->where('active', true)
            ->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $this->companyId))
            ->where('country', $this->country)
            ->orderBy('company_id', 'desc') // company-specific overrides global
            ->first();
    }

    private function applyRateCaps(float $amount, ?float $min, ?float $max): float
    {
        if ($min > 0 && $amount < $min) $amount = $min;
        if ($max > 0 && $amount > $max) $amount = $max;
        return $amount;
    }
}
