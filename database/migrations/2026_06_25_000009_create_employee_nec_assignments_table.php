<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeNecAssignmentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('employee_nec_assignments')) {
            return;
        }


        Schema::create('employee_nec_assignments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');

            $table->bigInteger('nec_category_id')->unsigned();
            $table->foreign('nec_category_id')->references('id')->on('nec_categories')->onDelete('cascade');

            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            $table->bigInteger('assigned_by')->unsigned()->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'effective_from']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_nec_assignments');
    }
}
