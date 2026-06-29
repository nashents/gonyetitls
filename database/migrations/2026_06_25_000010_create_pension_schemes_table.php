<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePensionSchemesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pension_schemes')) {
            return;
        }


        Schema::create('pension_schemes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->enum('type', ['defined_contribution', 'defined_benefit', 'provident_fund'])
                ->default('defined_contribution');
            $table->string('administrator_name')->nullable()
                ->comment('External fund administrator / insurance company');
            $table->string('fund_number')->nullable();
            $table->boolean('allows_voluntary_additional_contributions')->default(true);
            $table->boolean('active')->default(true);
            $table->text('description')->nullable();

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pension_schemes');
    }
}
