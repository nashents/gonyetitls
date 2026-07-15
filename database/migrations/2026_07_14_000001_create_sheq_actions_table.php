<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheqActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheq_actions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('actionable_type')->nullable();
            $table->bigInteger('actionable_id')->unsigned()->nullable();
            $table->string('action_number')->nullable();
            $table->string('source')->nullable();
            $table->string('reference')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('priority')->default('medium');
            $table->date('due_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->text('completion_notes')->nullable();
            $table->string('status')->default('open');
            $table->string('effectiveness')->nullable();
            $table->text('effectiveness_notes')->nullable();
            $table->bigInteger('verified_by_id')->unsigned()->nullable();
            $table->foreign('verified_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->date('verified_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['actionable_type', 'actionable_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sheq_actions');
    }
}
