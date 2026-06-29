<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds properly-typed decimal shadow columns alongside the existing string columns.
 *
 * Strategy: we do NOT drop the legacy string columns (that would break existing
 * Livewire components and exports). Instead we add `*_amount` decimal columns
 * that the new PayrollEngine writes to. A scheduled job or one-time command
 * can backfill them from the legacy columns.
 *
 * Once the new engine is fully live, a future migration can drop the string cols.
 */
class AlterSalariesAddDecimalColumns extends Migration
{
    public function up()
    {
        Schema::table('salaries', function (Blueprint $table) {
            // Link to payroll run
            $table->bigInteger('payroll_run_id')->unsigned()->nullable()->after('id');
            $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->onDelete('set null');

            // Proper decimal shadow columns
            $table->decimal('basic_amount', 15, 4)->default(0)->after('basic');
            $table->decimal('gross_amount', 15, 4)->default(0)->after('gross');
            $table->decimal('net_amount', 15, 4)->default(0)->after('net');
            $table->decimal('total_allowances_amount', 15, 4)->default(0)->after('total_allowances');
            $table->decimal('total_deductions_amount', 15, 4)->default(0)->after('total_deductions');

            // Statutory breakdown (new — previously missing entirely)
            $table->decimal('paye_amount', 15, 4)->default(0);
            $table->decimal('aids_levy_amount', 15, 4)->default(0);
            $table->decimal('nssa_employee_amount', 15, 4)->default(0);
            $table->decimal('nssa_employer_amount', 15, 4)->default(0);
            $table->decimal('pension_employee_amount', 15, 4)->default(0);
            $table->decimal('pension_employer_amount', 15, 4)->default(0);
            $table->decimal('nec_employee_amount', 15, 4)->default(0);
            $table->decimal('nec_employer_amount', 15, 4)->default(0);

            // Proration
            $table->decimal('proration_factor', 8, 6)->default(1.000000)
                ->comment('1.0 = full month; 0.5 = half month etc.');
            $table->unsignedSmallInteger('scheduled_days')->nullable();
            $table->unsignedSmallInteger('days_worked')->nullable();

            // Salary advance recovery this run
            $table->decimal('salary_advance_deduction', 15, 4)->default(0);

            // Reference to NEC and pension at time of calculation (snapshot)
            $table->bigInteger('nec_category_id')->unsigned()->nullable();
            $table->foreign('nec_category_id')->references('id')->on('nec_categories')->onDelete('set null');
            $table->bigInteger('pension_scheme_id')->unsigned()->nullable();
            $table->foreign('pension_scheme_id')->references('id')->on('pension_schemes')->onDelete('set null');

            // Lock flag — once payroll is locked this row must not change
            $table->boolean('is_locked')->default(false);
        });
    }

    public function down()
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropForeign(['payroll_run_id']);
            $table->dropForeign(['nec_category_id']);
            $table->dropForeign(['pension_scheme_id']);
            $table->dropColumn([
                'payroll_run_id',
                'basic_amount', 'gross_amount', 'net_amount',
                'total_allowances_amount', 'total_deductions_amount',
                'paye_amount', 'aids_levy_amount',
                'nssa_employee_amount', 'nssa_employer_amount',
                'pension_employee_amount', 'pension_employer_amount',
                'nec_employee_amount', 'nec_employer_amount',
                'proration_factor', 'scheduled_days', 'days_worked',
                'salary_advance_deduction',
                'nec_category_id', 'pension_scheme_id',
                'is_locked',
            ]);
        });
    }
}
