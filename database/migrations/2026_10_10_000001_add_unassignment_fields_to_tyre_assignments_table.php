<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tyre_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_assignments', 'unassigned_date')) {
                $table->date('unassigned_date')->nullable()->after('ending_odometer');
            }
            if (!Schema::hasColumn('tyre_assignments', 'unassignment_reason')) {
                $table->text('unassignment_reason')->nullable()->after('unassigned_date');
            }
            if (!Schema::hasColumn('tyre_assignments', 'unassigned_by')) {
                $table->bigInteger('unassigned_by')->nullable()->unsigned()->after('unassignment_reason');
            }
        });
    }

    public function down()
    {
        Schema::table('tyre_assignments', function (Blueprint $table) {
            foreach (['unassigned_by', 'unassignment_reason', 'unassigned_date'] as $column) {
                if (Schema::hasColumn('tyre_assignments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
