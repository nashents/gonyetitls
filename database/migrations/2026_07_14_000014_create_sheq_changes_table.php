<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheqChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheq_changes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('change_number')->nullable();
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->bigInteger('requested_by_id')->unsigned()->nullable();
            $table->foreign('requested_by_id')->references('id')->on('employees')->onDelete('cascade');
            $table->date('request_date')->nullable();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->text('reason')->nullable();
            $table->bigInteger('sheq_risk_assessment_id')->unsigned()->nullable();
            $table->foreign('sheq_risk_assessment_id')->references('id')->on('sheq_risk_assessments')->onDelete('cascade');
            $table->string('authorization')->default('pending');
            $table->bigInteger('authorized_by_id')->unsigned()->nullable();
            $table->foreign('authorized_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->text('reason_rejected')->nullable();
            $table->date('implementation_date')->nullable();
            $table->date('closeout_date')->nullable();
            $table->string('status')->default('open');
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
        Schema::dropIfExists('sheq_changes');
    }
}
