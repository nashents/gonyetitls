<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocumentControlToDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            // Document control (ISO 7.5 documented information)
            $table->string('document_number')->nullable();
            $table->string('revision')->nullable();
            $table->date('review_date')->nullable();
            $table->bigInteger('approved_by_id')->unsigned()->nullable();
            $table->string('approval_status')->nullable();
            $table->boolean('is_obsolete')->default(0);
            // SHEQ module attachments
            $table->bigInteger('sheq_obligation_id')->unsigned()->nullable();
            $table->bigInteger('sheq_audit_id')->unsigned()->nullable();
            $table->bigInteger('sheq_chemical_id')->unsigned()->nullable();
            $table->bigInteger('sheq_meeting_id')->unsigned()->nullable();
            $table->bigInteger('sheq_appointment_id')->unsigned()->nullable();
            $table->bigInteger('sheq_drill_id')->unsigned()->nullable();
            $table->bigInteger('sheq_risk_assessment_id')->unsigned()->nullable();
            $table->bigInteger('sheq_non_conformity_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('document_number');
            $table->dropColumn('revision');
            $table->dropColumn('review_date');
            $table->dropColumn('approved_by_id');
            $table->dropColumn('approval_status');
            $table->dropColumn('is_obsolete');
            $table->dropColumn('sheq_obligation_id');
            $table->dropColumn('sheq_audit_id');
            $table->dropColumn('sheq_chemical_id');
            $table->dropColumn('sheq_meeting_id');
            $table->dropColumn('sheq_appointment_id');
            $table->dropColumn('sheq_drill_id');
            $table->dropColumn('sheq_risk_assessment_id');
            $table->dropColumn('sheq_non_conformity_id');
        });
    }
}
