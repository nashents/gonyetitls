<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepartmentIdToEmployeePositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_positions', function (Blueprint $table) {
            $table->foreignId('branch_id')
                  ->constrained('branches')
                  ->onDelete('restrict');
            $table->foreignId('department_id')
                  ->constrained('departments')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_positions', function (Blueprint $table) {
              $table->dropForeign('branch_id');
              $table->dropColumn('branch_id');
              $table->dropForeign('department_id');
              $table->dropColumn('department_id');
        });
    }
}
