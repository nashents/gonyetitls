<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheqAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheq_audits', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('audit_number')->nullable();
            $table->bigInteger('sheq_audit_template_id')->unsigned()->nullable();
            $table->foreign('sheq_audit_template_id')->references('id')->on('sheq_audit_templates')->onDelete('cascade');
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->bigInteger('lead_auditor_id')->unsigned()->nullable();
            $table->foreign('lead_auditor_id')->references('id')->on('employees')->onDelete('cascade');
            $table->bigInteger('auditee_id')->unsigned()->nullable();
            $table->foreign('auditee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('audit_type')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->date('started_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->string('status')->default('planned');
            $table->text('summary')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sheq_audit_responses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sheq_audit_id')->unsigned()->nullable();
            $table->foreign('sheq_audit_id')->references('id')->on('sheq_audits')->onDelete('cascade');
            $table->bigInteger('sheq_audit_item_id')->unsigned()->nullable();
            $table->foreign('sheq_audit_item_id')->references('id')->on('sheq_audit_items')->onDelete('cascade');
            $table->string('grading')->nullable();
            $table->integer('actual_mark')->nullable();
            $table->text('findings')->nullable();
            $table->text('evidence')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['sheq_audit_id', 'sheq_audit_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sheq_audit_responses');
        Schema::dropIfExists('sheq_audits');
    }
}
