<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPayrollsAddRunColumns extends Migration
{
    public function up()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // Link to the new payroll_runs lifecycle table
            $table->bigInteger('payroll_run_id')->unsigned()->nullable()->after('id');
            $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->onDelete('set null');

            // Proper date fields alongside the legacy string month/year
            $table->date('period_start')->nullable()->after('year');
            $table->date('period_end')->nullable()->after('period_start');
            $table->date('payroll_date')->nullable()->after('period_end');

            // Frequency
            $table->bigInteger('payroll_frequency_id')->unsigned()->nullable()->after('payroll_date');
            $table->foreign('payroll_frequency_id')->references('id')->on('payroll_frequencies')->onDelete('set null');

            // Proper status lifecycle (the old authorization field remains for backward compat)
            $table->enum('payroll_status', [
                'draft', 'calculating', 'validated', 'approved',
                'locked', 'exported', 'posted', 'archived', 'reversed',
            ])->default('draft')->after('authorization');

            // Financial totals stored as proper decimals
            $table->decimal('total_gross_amount', 15, 4)->default(0)->after('payroll_status');
            $table->decimal('total_net_amount', 15, 4)->default(0)->after('total_gross_amount');
            $table->decimal('total_paye', 15, 4)->default(0)->after('total_net_amount');
            $table->decimal('total_nssa_employee', 15, 4)->default(0)->after('total_paye');
            $table->decimal('total_nssa_employer', 15, 4)->default(0)->after('total_nssa_employee');
            $table->decimal('total_pension_employee', 15, 4)->default(0)->after('total_nssa_employer');
            $table->decimal('total_pension_employer', 15, 4)->default(0)->after('total_pension_employee');
            $table->decimal('total_nec_employee', 15, 4)->default(0)->after('total_pension_employer');
            $table->decimal('total_nec_employer', 15, 4)->default(0)->after('total_nec_employee');

            // Lifecycle users
            $table->bigInteger('calculated_by')->unsigned()->nullable()->after('authorized_by_id');
            $table->bigInteger('locked_by')->unsigned()->nullable()->after('calculated_by');
            $table->bigInteger('posted_by')->unsigned()->nullable()->after('locked_by');
            $table->timestamp('locked_at')->nullable()->after('posted_by');
            $table->timestamp('posted_at')->nullable()->after('locked_at');
        });
    }

    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['payroll_run_id']);
            $table->dropForeign(['payroll_frequency_id']);
            $table->dropColumn([
                'payroll_run_id', 'period_start', 'period_end', 'payroll_date',
                'payroll_frequency_id', 'payroll_status',
                'total_gross_amount', 'total_net_amount', 'total_paye',
                'total_nssa_employee', 'total_nssa_employer',
                'total_pension_employee', 'total_pension_employer',
                'total_nec_employee', 'total_nec_employer',
                'calculated_by', 'locked_by', 'posted_by', 'locked_at', 'posted_at',
            ]);
        });
    }
}
