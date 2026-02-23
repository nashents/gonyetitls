<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecruitmentScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruitment_scores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('candidate_id')->constrained('recruitment_candidates')->cascadeOnDelete();
            $table->foreignId('scored_by')->nullable()->constrained('users')->nullOnDelete();

            // screening, road_test, final
            $table->string('stage');

            // e.g. attitude, communication, experience_fit, defensive_driving, etc
            $table->string('criterion');

            $table->unsignedTinyInteger('score_percent')->nullable(); // 0..100
            $table->unsignedTinyInteger('weight')->nullable();        // 0..100 optional

            $table->text('comment')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['candidate_id', 'stage']);
            $table->index(['stage', 'criterion']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruitment_scores');
    }
}
