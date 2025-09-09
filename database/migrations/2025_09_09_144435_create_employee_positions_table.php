<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeePositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('employee_positions', function (Blueprint $table) {
            $table->id();
               // Employee link
            $table->foreignId('employee_id')
                 ->nullable()
                   ->unsigned()
                  ->constrained('employees')
                  ->onDelete('cascade');

            // Link to standardized job title
            $table->foreignId('job_title_id')
                  ->nullable()
                    ->unsigned()
                  ->constrained('job_titles')
                  ->onDelete('restrict');

            $table->foreignId('rank_id')
                  ->nullable()
                    ->unsigned()
                  ->constrained('ranks')
                  ->onDelete('restrict');

            // Link to standardized grade
            $table->foreignId('grade_id')
                  ->nullable()
                    ->unsigned()
                  ->constrained('grades')
                  ->onDelete('restrict');

            $table->foreignId('branch_id')
                  ->nullable()
                    ->unsigned()
                  ->constrained('branches')
                  ->onDelete('restrict');

            $table->foreignId('department_id')
                  ->nullable()
                  ->unsigned()
                  ->constrained('departments')
                  ->onDelete('restrict');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('change_reason')->nullable();

            $table->foreignId('changed_by')
                  ->nullable()
                    ->unsigned()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_positions');
    }
}
