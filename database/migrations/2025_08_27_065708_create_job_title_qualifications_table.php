<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobTitleQualificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_title_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_title_id')->constrained('job_titles')->cascadeOnDelete();
            $table->foreignId('qualification_id')->constrained('qualifications')->cascadeOnDelete();

            // Matrix attributes
            $table->boolean('mandatory')->default(true);           // required vs nice-to-have
            $table->unsignedTinyInteger('min_level')->nullable();  // minimum acceptable level
            $table->unsignedSmallInteger('weight')->default(100);  // scoring weight (0–100)
            $table->unsignedSmallInteger('min_score')->nullable(); // if you score quals
            $table->timestamps();

            $table->unique(['job_title_id','qualification_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_title_qualifications');
    }
}
