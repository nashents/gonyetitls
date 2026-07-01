<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\EmployeeNecAssignment;
use App\Models\EmployeePensionEnrollment;
use App\Models\Employee;
use App\Models\NecCategory;
use App\Models\PayrollCompanyConfig;
use App\Models\PayrollFrequency;
use App\Models\PensionScheme;
use App\Models\StatutoryDeductionType;
use Illuminate\Database\Seeder;

/**
 * Seeds the minimum statutory configuration needed for the payroll
 * calculation engine (StatutoryCalculator) to run end to end: a payroll
 * frequency, a per-company config, and example PAYE/NSSA/NEC/Pension rates.
 *
 * IMPORTANT: the rate figures below (PAYE bands, NSSA percentage/ceiling,
 * NEC/Pension example rates) are illustrative placeholders sized to exercise
 * every branch of the calculator, NOT a verified current ZIMRA/NSSA table.
 * Finance must confirm and update these via the PayrollConfig UI (or a
 * follow-up data migration) against the current official rates before this
 * is used for real payroll processing.
 */
class PayrollStatutoryConfigSeeder extends Seeder
{
    public function run(): void
    {
        $frequency = PayrollFrequency::firstOrCreate(
            ['code' => 'MONTHLY'],
            ['name' => 'Monthly', 'periods_per_year' => 12, 'active' => true]
        );

        foreach (Company::all() as $company) {
            PayrollCompanyConfig::firstOrCreate(
                ['company_id' => $company->id],
                [
                    'country'                 => 'ZW',
                    'currency_id'             => $company->currency_id,
                    'tax_authority_name'      => 'ZIMRA',
                    'social_security_authority_name' => 'NSSA',
                    'proration_method'        => 'calendar_days',
                    'work_week_days'          => [1, 2, 3, 4, 5],
                    'allow_negative_net_pay'  => false,
                    'auto_deduct_loans'       => true,
                    'auto_deduct_salary_advances' => true,
                    'effective_from'          => now()->startOfYear(),
                    'active'                  => true,
                ]
            );
        }

        $effectiveFrom = now()->startOfYear()->toDateString();

        // ── PAYE (bracket-based, system-wide) ──────────────────────────────
        $paye = StatutoryDeductionType::firstOrCreate(
            ['code' => 'PAYE', 'country' => 'ZW', 'company_id' => null],
            ['name' => 'Pay As You Earn', 'calculation_type' => 'bracket', 'applies_to' => 'employee', 'active' => true]
        );

        if ($paye->taxBrackets()->count() === 0) {
            $bands = [
                ['lower' => 0,      'upper' => 1200,   'rate' => 0,  'aids' => 0],
                ['lower' => 1200,   'upper' => 3600,   'rate' => 20, 'aids' => 3],
                ['lower' => 3600,   'upper' => 12000,  'rate' => 25, 'aids' => 3],
                ['lower' => 12000,  'upper' => 24000,  'rate' => 30, 'aids' => 3],
                ['lower' => 24000,  'upper' => 36000,  'rate' => 35, 'aids' => 3],
                ['lower' => 36000,  'upper' => null,   'rate' => 40, 'aids' => 3],
            ];
            foreach ($bands as $i => $band) {
                $paye->taxBrackets()->create([
                    'effective_from'    => $effectiveFrom,
                    'lower_band'        => $band['lower'],
                    'upper_band'        => $band['upper'],
                    'rate_percentage'   => $band['rate'],
                    'aids_levy_percentage' => $band['aids'],
                    'sort_order'        => $i,
                ]);
            }
        }

        // ── NSSA (percentage with ceiling, system-wide) ─────────────────────
        $nssa = StatutoryDeductionType::firstOrCreate(
            ['code' => 'NSSA', 'country' => 'ZW', 'company_id' => null],
            ['name' => 'National Social Security Authority', 'calculation_type' => 'percentage', 'applies_to' => 'both', 'active' => true]
        );
        if ($nssa->rates()->count() === 0) {
            $nssa->rates()->create([
                'effective_from'     => $effectiveFrom,
                'employee_percentage'=> 4.5,
                'employer_percentage'=> 4.5,
                'earnings_ceiling'   => 5000,
            ]);
        }

        // ── AIDS Levy type record (rate is carried on the PAYE bracket) ─────
        StatutoryDeductionType::firstOrCreate(
            ['code' => 'AIDS_LEVY', 'country' => 'ZW', 'company_id' => null],
            ['name' => 'AIDS Levy', 'calculation_type' => 'percentage', 'applies_to' => 'employee', 'active' => true]
        );

        // ── PENSION type record (used for the is_pre_tax flag lookup) ───────
        StatutoryDeductionType::firstOrCreate(
            ['code' => 'PENSION', 'country' => 'ZW', 'company_id' => null],
            ['name' => 'Pension', 'calculation_type' => 'percentage', 'applies_to' => 'both', 'is_pre_tax' => true, 'active' => true]
        );

        // ── NEC: one example category per company ───────────────────────────
        foreach (Company::all() as $company) {
            $nec = NecCategory::firstOrCreate(
                ['company_id' => $company->id, 'code' => 'GEN'],
                ['country' => 'ZW', 'name' => 'General NEC', 'active' => true]
            );
            if ($nec->rates()->count() === 0) {
                $nec->rates()->create([
                    'effective_from'      => $effectiveFrom,
                    'employee_percentage' => 1.0,
                    'employer_percentage' => 2.0,
                    'calculation_basis'   => 'basic_salary',
                ]);
            }

            // ── Pension: one example scheme per company ──────────────────────
            $pension = PensionScheme::firstOrCreate(
                ['company_id' => $company->id, 'code' => 'GEN'],
                ['name' => 'Company Pension Fund', 'type' => 'defined_contribution', 'active' => true]
            );
            if ($pension->rates()->count() === 0) {
                $pension->rates()->create([
                    'effective_from'      => $effectiveFrom,
                    'employee_percentage' => 5.0,
                    'employer_percentage' => 7.5,
                    'calculation_basis'   => 'basic_salary',
                ]);
            }
        }
    }
}
