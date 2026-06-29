<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollFrequenciesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('payroll_frequencies')) {
            return;
        }


        Schema::create('payroll_frequencies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned()->nullable()
                ->comment('NULL = global/system default');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->string('name');                          // Monthly, Fortnightly, Weekly, etc.
            $table->string('code')->unique();                // MONTHLY, FORTNIGHTLY, WEEKLY, BIWEEKLY, DAILY, CONTRACT
            $table->unsignedTinyInteger('periods_per_year')->default(12);
            $table->unsignedTinyInteger('days_in_period')->nullable()
                ->comment('Null = variable (monthly). 14 = fortnightly, 7 = weekly, etc.');
            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_frequencies');
    }
}
