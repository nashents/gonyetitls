<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecruitmentDecisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruitment_decisions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('candidate_id')->constrained('recruitment_candidates')->cascadeOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();

            // screening, road_test, final
            $table->string('stage');

            // IMPRESSED, UNIMPRESSED, ROAD_TEST, DECLINE, ENGAGE, CONTRACTED, HIRED
            $table->string('decision');

            $table->text('comment')->nullable();
            $table->dateTime('decided_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['candidate_id', 'stage']);
            $table->index(['decision']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruitment_decisions');
    }
}
