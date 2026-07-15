<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheqContractorOnboardingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * SHEQ onboarding status layered over EXISTING contractor entities
     * (vendors and transporters) - no duplicate contractor master data.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheq_contractor_onboardings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('contractorable_type')->nullable();
            $table->bigInteger('contractorable_id')->unsigned()->nullable();
            $table->date('induction_date')->nullable();
            $table->date('induction_expiry')->nullable();
            $table->string('screening_status')->default('pending');
            $table->string('sheq_file_status')->default('pending');
            $table->string('sheq_score')->nullable();
            $table->date('last_audit_date')->nullable();
            $table->date('next_audit_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['contractorable_type', 'contractorable_id'], 'sheq_contractor_onboardings_contractorable_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sheq_contractor_onboardings');
    }
}
