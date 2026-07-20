<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMaximumLeaveDaysToLeaveTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->decimal('maximum_leave_days', 8, 2)
                ->default(0)
                ->after('max_consecutive_days');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('maximum_leave_days');
        });
    }
}
