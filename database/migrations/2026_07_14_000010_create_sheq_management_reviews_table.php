<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheqManagementReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheq_management_reviews', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('review_number')->nullable();
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->bigInteger('chairperson_id')->unsigned()->nullable();
            $table->foreign('chairperson_id')->references('id')->on('employees')->onDelete('cascade');
            $table->date('review_date')->nullable();
            $table->text('attendees')->nullable();
            $table->text('audit_results')->nullable();
            $table->text('customer_feedback')->nullable();
            $table->text('process_performance')->nullable();
            $table->text('incident_nc_status')->nullable();
            $table->text('action_status')->nullable();
            $table->text('objective_progress')->nullable();
            $table->text('compliance_status')->nullable();
            $table->text('risks_opportunities')->nullable();
            $table->text('resource_adequacy')->nullable();
            $table->text('improvement_opportunities')->nullable();
            $table->text('decisions')->nullable();
            $table->string('status')->default('scheduled');
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
        Schema::dropIfExists('sheq_management_reviews');
    }
}
