<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecruitmentChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruitment_checks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('candidate_id')->constrained('recruitment_candidates')->cascadeOnDelete();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();

            // criminal_record, reference, medical, licence_verification, etc
            $table->string('type');

            // NO_CRIMINAL_RECORD, NOT_AVAILABLE, FAIL, PASS, PENDING, etc
            $table->string('result')->nullable();

            $table->text('comment')->nullable();
            $table->date('checked_at')->nullable();

            // optional file link if you store docs
            $table->string('attachment_path')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['candidate_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruitment_checks');
    }
}
