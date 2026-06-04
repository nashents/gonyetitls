<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeaveBalanceFieldsToEmployeeLeavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_leaves', function (Blueprint $table) {

            $table->decimal('opening_balance', 8, 2)
                ->default(0)
                ->after('maximum_leave_days');

            $table->decimal('carried_forward_days', 8, 2)
                ->default(0)
                ->after('opening_balance');

            $table->decimal('days_accrued', 8, 2)
                ->default(0)
                ->after('carried_forward_days');

            $table->decimal('days_taken', 8, 2)
                ->default(0)
                ->after('days_accrued');

            $table->decimal('days_adjusted', 8, 2)
                ->default(0)
                ->after('days_taken');

            $table->date('last_accrual_date')
                ->nullable()
                ->after('days_adjusted');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_leaves', function (Blueprint $table) {
            $table->dropColumn([
                'opening_balance',
                'carried_forward_days',
                'days_accrued',
                'days_taken',
                'days_adjusted',
                'last_accrual_date',
            ]);
        });
    }
}