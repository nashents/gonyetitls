<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheqChemicalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheq_chemicals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('trade_name')->nullable();
            $table->string('supplier')->nullable();
            $table->string('hazard_class')->nullable();
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('storage_location')->nullable();
            $table->string('quantity')->nullable();
            $table->string('unit_of_measure')->nullable();
            $table->boolean('sds_available')->default(0);
            $table->date('sds_review_date')->nullable();
            $table->boolean('storage_bunded')->default(0);
            $table->boolean('spill_kit_available')->default(0);
            $table->text('incompatible_with')->nullable();
            $table->text('ppe_required')->nullable();
            $table->bigInteger('coordinator_id')->unsigned()->nullable();
            $table->foreign('coordinator_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('licence_required')->nullable();
            $table->string('status')->default('in_use');
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
        Schema::dropIfExists('sheq_chemicals');
    }
}
