<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeePensionEnrollmentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('employee_pension_enrollments')) {
            return;
        }


        Schema::create('employee_pension_enrollments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');

            $table->bigInteger('pension_scheme_id')->unsigned();
            $table->foreign('pension_scheme_id')->references('id')->on('pension_schemes')->onDelete('cascade');

            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            // Optional voluntary additional contributions on top of scheme rate
            $table->decimal('voluntary_additional_percentage', 8, 4)->default(0);
            $table->decimal('voluntary_additional_fixed_amount', 15, 4)->default(0);

            $table->string('member_number')->nullable()
                ->comment('Employee membership number with the pension fund');

            $table->bigInteger('enrolled_by')->unsigned()->nullable();
            $table->foreign('enrolled_by')->references('id')->on('users')->onDelete('set null');

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'effective_from']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_pension_enrollments');
    }
}
