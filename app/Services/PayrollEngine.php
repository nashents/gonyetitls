<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeNecAssignment;
use App\Models\EmployeePensionEnrollment;
use App\Models\Loan;
use App\Models\PayrollAuditLog;
use App\Models\PayrollCompanyConfig;
use App\Models\PayrollRun;
use App\Models\PayrollSalary;
use App\Models\PublicHoliday;
use App\Models\SalaryAdvance;
use App\Models\StatutoryDeductionType;
use App\Models\TaxBracketV2;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PayrollEngine
 *
 * Configuration-driven payroll calculation engine.
 * All statutory rates, NEC, pension, PAYE brackets, NSSA ceilings
 * are resolved from the database using the payroll_date of the run —
 * never hardcoded.
 *
 * Supports: ZW, ZM, BW, NA, ZA, MZ, MW, TZ (any country with configured rates).
 */
class PayrollEngine
{
    private PayrollRun $run;
    private PayrollCompanyConfig $config;
    private string $payrollDate;
    private string $country;

    // Cached statutory types for this run
    private ?StatutoryDeductionType $payeType    = null;
    private ?StatutoryDeductionType $nssaType     = null;
    private ?StatutoryDeductionType $aidsLevyType = null;

    public function __construct(PayrollRun $run)
    {
        $this->run         = $run;
        $this->payrollDate = $run->payroll_date->toDateString();
        $this->config      = PayrollCompanyConfig::where('company_id', $run->company_id)
            ->where('active', true)
            ->where('effective_from', '<=', $this->payrollDate)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $this->payrollDate))
            ->orderByDesc('effective_from')
            ->firstOrFail();
        $this->country = $this->config->country ?? 'ZW';
    }

    // ════════════════════════════════════════════════════════════════════════
    // PUBLIC ENTRY POINT
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Calculate payroll for all employees in this run.
     * Wraps everything in a transaction — either all succeed or none do.
     */
    public function calculate(): void
    {
        $this->run->update(['status' => 'calculating', 'calculated_by' => auth()->id(), 'calculated_at' => now()]);

        DB::transaction(function () {
            $employees = Employee::where('company_id', $this->run->company_id)
                ->where('archive', false)
                ->where('status', true)
                ->whereHas('salaries', fn ($q) => $q->where('payroll_run_id', $this->run->id))
                ->with([
                    'salaries'    => fn ($q) => $q->where('payroll_run_id', $this->run->id),
                    'loans'       => fn ($q) => $q->where('authorization', 'approved')->where('status', 'Unpaid'),
                    'salaryAdvances' => fn ($q) => $q->where('authorization', 'approved')->where('status', 'Unpaid'),
                ])
                ->get();

            $totals = [
                'employee_count'            => 0,
                'total_gross'               => 0,
                'total_net'                 => 0,
                'total_deductions'          => 0,
                'total_employer_contributions' => 0,
            ];

            foreach ($employees as $employee) {
                $salary = $employee->salaries->first();
                if (!$salary) continue;

                $result = $this->calculateForEmployee($employee, $salary);

                $totals['employee_count']++;
                $totals['total_gross']    += $result['gross_amount'];
                $totals['total_net']      += $result['net_amount'];
                $totals['total_deductions'] += $result['total_deductions_amount'];
                $totals['total_employer_contributions'] +=
                    $result['nssa_employer_amount'] + $result['pension_employer_amount'] + $result['nec_employer_amount'];
            }

            $this->run->update(array_merge($totals, ['status' => 'validated', 'validated_by' => auth()->id(), 'validated_at' => now()]));
        });

        PayrollAuditLog::record('calculated', $this->run->id);
    }

    // ════════════════════════════════════════════════════════════════════════
    // PER-EMPLOYEE CALCULATION
    // ════════════════════════════════════════════════════════════════════════

    private function calculateForEmployee(Employee $employee, PayrollSalary $salary): array
    {
        // 1. Proration
        $prorationFactor = $this->computeProration($employee);

        // 2. Gross = (basic + allowances) × proration
        $basic       = (float) $salary->basic;
        $allowances  = (float) $salary->total_allowances;
        $grossBefore = ($basic + $allowances) * $prorationFactor;

        // 3. Pre-tax deductions (e.g. pension if pre-tax configured)
        $pensionData    = $this->computePension($employee, $grossBefore);
        $preTaxDeduct   = $pensionData['is_pre_tax'] ? $pensionData['employee_amount'] : 0;
        $taxableGross   = $grossBefore - $preTaxDeduct;

        // 4. NSSA
        $nssaData = $this->computeNssa($taxableGross);

        // 5. NEC
        $necData = $this->computeNec($employee, $grossBefore);

        // 6. PAYE (on taxable gross after NSSA employee if pre-tax)
        $payeData = $this->computePaye($taxableGross - ($nssaData['is_pre_tax'] ? $nssaData['employee_amount'] : 0));

        // 7. Post-tax pension (if not pre-tax)
        if (!$pensionData['is_pre_tax']) {
            // pension already calculated above; nothing extra here
        }

        // 8. Loan deductions
        $loanDeduction = $this->computeLoanDeductions($employee);

        // 9. Salary advance deductions
        $advanceDeduction = $this->computeAdvanceDeductions($employee);

        // 10. Other deductions from the salary record
        $otherDeductions = (float) $salary->total_deductions;

        // 11. Net pay
        $totalStatutoryEmployee =
            $payeData['paye'] +
            $payeData['aids_levy'] +
            $nssaData['employee_amount'] +
            $necData['employee_amount'] +
            $pensionData['employee_amount'];

        $totalDeductions = $totalStatutoryEmployee + $loanDeduction + $advanceDeduction + $otherDeductions;
        $netPay          = $grossBefore - $totalDeductions;

        if (!$this->config->allow_negative_net_pay && $netPay < 0) {
            $netPay = 0;
        }

        $result = [
            'basic_amount'            => $basic * $prorationFactor,
            'gross_amount'            => $grossBefore,
            'net_amount'              => $netPay,
            'total_allowances_amount' => $allowances * $prorationFactor,
            'total_deductions_amount' => $totalDeductions,
            'paye_amount'             => $payeData['paye'],
            'aids_levy_amount'        => $payeData['aids_levy'],
            'nssa_employee_amount'    => $nssaData['employee_amount'],
            'nssa_employer_amount'    => $nssaData['employer_amount'],
            'pension_employee_amount' => $pensionData['employee_amount'],
            'pension_employer_amount' => $pensionData['employer_amount'],
            'nec_employee_amount'     => $necData['employee_amount'],
            'nec_employer_amount'     => $necData['employer_amount'],
            'salary_advance_deduction'=> $advanceDeduction,
            'proration_factor'        => $prorationFactor,
            'nec_category_id'         => $necData['nec_category_id'],
            'pension_scheme_id'       => $pensionData['pension_scheme_id'],
            'is_locked'               => false,
        ];

        $salary->update($result);

        // Deduct loan balances
        $this->applyLoanDeductions($employee, $loanDeduction);

        // Deduct advance balances
        $this->applyAdvanceDeductions($employee, $advanceDeduction);

        return $result;
    }

    // ════════════════════════════════════════════════════════════════════════
    // PRORATION
    // ════════════════════════════════════════════════════════════════════════

    private function computeProration(Employee $employee): float
    {
        $method = $this->run->proration_method ?? $this->config->proration_method;

        // If employee has a full period contract, no proration needed
        // (In production: check employee start_date vs period)
        $periodStart = $this->run->period_start;
        $periodEnd   = $this->run->period_end;

        return match ($method) {
            'fixed_30'       => $this->prorateFixed30($employee, $periodStart, $periodEnd),
            'calendar_days'  => $this->prorateCalendarDays($employee, $periodStart, $periodEnd),
            'working_days'   => $this->prorateWorkingDays($employee, $periodStart, $periodEnd),
            'scheduled_days' => $this->prorateScheduledDays($employee, $periodStart, $periodEnd),
            default          => 1.0,
        };
    }

    private function prorateFixed30(Employee $employee, Carbon $start, Carbon $end): float
    {
        $employeeStart = $this->employeeStartInPeriod($employee, $start, $end);
        if ($employeeStart->eq($start)) return 1.0;
        $daysWorked = $employeeStart->diffInDays($end) + 1;
        return min(1.0, round($daysWorked / 30, 6));
    }

    private function prorateCalendarDays(Employee $employee, Carbon $start, Carbon $end): float
    {
        $employeeStart = $this->employeeStartInPeriod($employee, $start, $end);
        if ($employeeStart->eq($start)) return 1.0;
        $totalDays  = $start->diffInDays($end) + 1;
        $workedDays = $employeeStart->diffInDays($end) + 1;
        return min(1.0, round($workedDays / $totalDays, 6));
    }

    private function prorateWorkingDays(Employee $employee, Carbon $start, Carbon $end): float
    {
        $employeeStart = $this->employeeStartInPeriod($employee, $start, $end);
        $workWeek   = $this->config->work_week_days ?? [1, 2, 3, 4, 5];
        $excludePH  = $this->config->exclude_public_holidays_from_proration;
        $companyId  = $this->run->company_id;

        $totalWorkDays  = $this->countWorkingDays($start, $end, $workWeek, $excludePH, $companyId);
        $workedWorkDays = $this->countWorkingDays($employeeStart, $end, $workWeek, $excludePH, $companyId);

        return $totalWorkDays > 0 ? min(1.0, round($workedWorkDays / $totalWorkDays, 6)) : 1.0;
    }

    private function prorateScheduledDays(Employee $employee, Carbon $start, Carbon $end): float
    {
        // Placeholder — would query employee roster/shift schedule
        return $this->prorateWorkingDays($employee, $start, $end);
    }

    private function employeeStartInPeriod(Employee $employee, Carbon $periodStart, Carbon $periodEnd): Carbon
    {
        $joinDate = $employee->date_joined ? Carbon::parse($employee->date_joined) : null;
        if ($joinDate && $joinDate->gt($periodStart) && $joinDate->lte($periodEnd)) {
            return $joinDate;
        }
        return $periodStart->copy();
    }

    private function countWorkingDays(Carbon $from, Carbon $to, array $workWeek, bool $excludePH, int $companyId): int
    {
        $count   = 0;
        $current = $from->copy();
        while ($current->lte($to)) {
            $isWorkDay = in_array($current->dayOfWeekIso, $workWeek);
            $isHoliday = $excludePH && PublicHoliday::isHoliday($current, $this->country, $companyId);
            if ($isWorkDay && !$isHoliday) {
                $count++;
            }
            $current->addDay();
        }
        return $count;
    }

    // ════════════════════════════════════════════════════════════════════════
    // PAYE (bracket-based, config-driven)
    // ════════════════════════════════════════════════════════════════════════

    private function computePaye(float $annualisedMonthly): array
    {
        $type = $this->getStatutoryType('PAYE');
        if (!$type) return ['paye' => 0.0, 'aids_levy' => 0.0];

        // Get brackets effective on payroll_date
        $brackets = TaxBracketV2::where('statutory_deduction_type_id', $type->id)
            ->where('effective_from', '<=', $this->payrollDate)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $this->payrollDate))
            ->whereNull('deleted_at')
            ->orderBy('lower_band')
            ->get();

        if ($brackets->isEmpty()) return ['paye' => 0.0, 'aids_levy' => 0.0];

        $annualIncome = $annualisedMonthly * 12;
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

            // AIDS Levy is applied on top of the PAYE calculated in this bracket
            if ((float) $bracket->aids_levy_percentage > 0) {
                $aidsLevy += $taxInBand * ((float) $bracket->aids_levy_percentage / 100);
            }
        }

        // Convert annual back to period (monthly = /12, fortnightly = /26, etc.)
        $periodsPerYear = $this->run->frequency?->periods_per_year ?? 12;
        $periodPaye     = round($annualPaye / $periodsPerYear, 4);
        $periodAidsLevy = round($aidsLevy   / $periodsPerYear, 4);

        return ['paye' => $periodPaye, 'aids_levy' => $periodAidsLevy];
    }

    // ════════════════════════════════════════════════════════════════════════
    // NSSA (ceiling-based, config-driven)
    // ════════════════════════════════════════════════════════════════════════

    private function computeNssa(float $earnings): array
    {
        $type = $this->getStatutoryType('NSSA');
        if (!$type) return ['employee_amount' => 0.0, 'employer_amount' => 0.0, 'is_pre_tax' => false];

        $rate = $type->rateOn($this->payrollDate);
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

    private function computeNec(Employee $employee, float $earnings): array
    {
        $empty = ['employee_amount' => 0.0, 'employer_amount' => 0.0, 'nec_category_id' => null];

        $assignment = EmployeeNecAssignment::where('employee_id', $employee->id)
            ->where('effective_from', '<=', $this->payrollDate)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $this->payrollDate))
            ->with('necCategory')
            ->latest('effective_from')
            ->first();

        if (!$assignment) return $empty;

        $category = $assignment->necCategory;
        $rate     = $category->rateOn($this->payrollDate);
        if (!$rate) return $empty;

        $basis    = match ($rate->calculation_basis) {
            'gross_salary'         => $earnings,
            'pensionable_earnings' => $earnings,  // simplification
            default                => $earnings,  // basic_salary — already prorated
        };

        $ceiling  = $rate->earnings_ceiling ?? PHP_FLOAT_MAX;
        $insured  = min($basis, (float) $ceiling);

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

    private function computePension(Employee $employee, float $earnings): array
    {
        $empty = ['employee_amount' => 0.0, 'employer_amount' => 0.0, 'pension_scheme_id' => null, 'is_pre_tax' => false];

        $enrollment = EmployeePensionEnrollment::where('employee_id', $employee->id)
            ->where('effective_from', '<=', $this->payrollDate)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $this->payrollDate))
            ->with('pensionScheme')
            ->latest('effective_from')
            ->first();

        if (!$enrollment) return $empty;

        $scheme = $enrollment->pensionScheme;
        $rate   = $scheme->rateOn($this->payrollDate);
        if (!$rate) return $empty;

        $ceiling  = $rate->earnings_ceiling ?? PHP_FLOAT_MAX;
        $insured  = min($earnings, (float) $ceiling);

        $empAmount = $rate->employee_percentage > 0
            ? $insured * ((float) $rate->employee_percentage / 100)
            : (float) $rate->employee_fixed_amount;

        // Add voluntary additional contributions
        $voluntary = (float) $enrollment->voluntary_additional_percentage > 0
            ? $insured * ((float) $enrollment->voluntary_additional_percentage / 100)
            : (float) $enrollment->voluntary_additional_fixed_amount;

        $empAmount += $voluntary;

        // Employer contribution
        $emplrAmount = $rate->employer_percentage > 0
            ? $insured * ((float) $rate->employer_percentage / 100)
            : (float) $rate->employer_fixed_amount;

        // Employer match (if applicable)
        if ($rate->employer_match_percentage > 0) {
            $matchable   = $insured * ((float) $rate->employer_match_percentage / 100);
            $matchCap    = $rate->employer_match_cap ?? PHP_FLOAT_MAX;
            $matchAmount = min($empAmount, $matchable, (float) $matchCap);
            $emplrAmount += $matchAmount;
        }

        // Apply caps
        $empAmount   = $this->applyRateCaps($empAmount, (float) $rate->minimum_employee_contribution, (float) $rate->maximum_employee_contribution);
        $emplrAmount = $this->applyRateCaps($emplrAmount, (float) $rate->minimum_employer_contribution, (float) $rate->maximum_employer_contribution);

        // Pre-tax flag comes from the pension type in statutory_deduction_types
        $pensionStatType = $this->getStatutoryType('PENSION');
        $isPreTax = $pensionStatType?->is_pre_tax ?? false;

        return [
            'employee_amount'  => round($empAmount, 4),
            'employer_amount'  => round($emplrAmount, 4),
            'pension_scheme_id'=> $scheme->id,
            'is_pre_tax'       => $isPreTax,
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // LOANS & ADVANCES
    // ════════════════════════════════════════════════════════════════════════

    private function computeLoanDeductions(Employee $employee): float
    {
        if (!$this->config->auto_deduct_loans) return 0.0;

        return (float) $employee->loans
            ->filter(fn ($l) => $l->authorization === 'approved' && $l->status === 'Unpaid')
            ->sum(fn ($loan) => min(
                (float) ($loan->monthly_installment ?? $loan->balance),
                (float) $loan->balance
            ));
    }

    private function applyLoanDeductions(Employee $employee, float $totalDeducted): void
    {
        if ($totalDeducted <= 0) return;
        $remaining = $totalDeducted;
        foreach ($employee->loans->where('authorization', 'approved')->where('status', 'Unpaid') as $loan) {
            if ($remaining <= 0) break;
            $installment = min((float) ($loan->monthly_installment ?? $loan->balance), (float) $loan->balance, $remaining);
            $newBalance  = max(0, (float) $loan->balance - $installment);
            $loan->update([
                'balance' => (string) $newBalance,
                'status'  => $newBalance <= 0 ? 'Paid' : 'Unpaid',
            ]);
            $remaining -= $installment;
        }
    }

    private function computeAdvanceDeductions(Employee $employee): float
    {
        if (!$this->config->auto_deduct_salary_advances) return 0.0;
        return (float) $employee->salaryAdvances->sum(fn ($a) => $a->recoveryThisRun());
    }

    private function applyAdvanceDeductions(Employee $employee, float $totalDeducted): void
    {
        if ($totalDeducted <= 0) return;
        $remaining = $totalDeducted;
        foreach ($employee->salaryAdvances as $advance) {
            if ($remaining <= 0) break;
            $recovery   = min($advance->recoveryThisRun(), $remaining);
            $newBalance = max(0, (float) $advance->balance - $recovery);
            $advance->update([
                'balance' => $newBalance,
                'status'  => $newBalance <= 0 ? 'Paid' : ($newBalance < (float) $advance->amount ? 'Partially Paid' : 'Unpaid'),
            ]);
            $remaining -= $recovery;
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    // LIFECYCLE ACTIONS
    // ════════════════════════════════════════════════════════════════════════

    public function approve(string $reason = ''): void
    {
        if (!$this->run->canBeApproved()) {
            throw new \RuntimeException("Payroll run {$this->run->id} cannot be approved in status: {$this->run->status}");
        }
        $this->run->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        PayrollAuditLog::record('approved', $this->run->id, [], [], $reason);
    }

    public function lock(): void
    {
        if ($this->run->status !== 'approved') {
            throw new \RuntimeException("Only approved payroll runs can be locked.");
        }
        // Lock all salary lines
        PayrollSalary::where('payroll_run_id', $this->run->id)->update(['is_locked' => true]);
        $this->run->update([
            'status'    => 'locked',
            'locked_by' => auth()->id(),
            'locked_at' => now(),
        ]);
        PayrollAuditLog::record('locked', $this->run->id);
    }

    public function markPosted(): void
    {
        $this->run->update([
            'status'    => 'posted',
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);
        PayrollAuditLog::record('posted', $this->run->id);
    }

    // ════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════════════════════

    private function getStatutoryType(string $code): ?StatutoryDeductionType
    {
        return StatutoryDeductionType::where('code', $code)
            ->where('active', true)
            ->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $this->run->company_id))
            ->where('country', $this->country)
            ->orderBy('company_id', 'desc') // company-specific overrides global
            ->first();
    }

    private function applyRateCaps(float $amount, float $min, float $max): float
    {
        if ($min > 0 && $amount < $min) $amount = $min;
        if ($max > 0 && $amount > $max) $amount = $max;
        return $amount;
    }
}
