<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();

            // Basic Identification
            $table->string('grade_code')->unique();   // e.g., A1, B2
            $table->string('grade_name');             // e.g., Senior Manager
            $table->unsignedInteger('grade_level');   // Numeric ordering

            // Compensation
            $table->decimal('min_salary', 12, 2)->nullable();
            $table->decimal('max_salary', 12, 2)->nullable();
            $table->foreignId('currency_id')->constrained('currencies'); // FK to currencies

            // Job & Role Structure
            $table->string('job_category')->nullable(); 
            $table->string('job_band')->nullable();     

            // Promotion & Progression
            $table->foreignId('next_grade_id')->nullable()->constrained('grades'); 
            $table->text('promotion_criteria')->nullable();
            $table->unsignedInteger('max_years_in_grade')->nullable();

            // Benefits & Perks
            $table->unsignedInteger('leave_days')->default(30);
            $table->boolean('bonus_eligibility')->default(false);
            $table->boolean('overtime_eligibility')->default(true);
            $table->string('benefits_package')->nullable();

            // Administrative
            $table->date('effective_date')->nullable();
            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });

        // Pivot table for many-to-many: grade <-> job_titles
        Schema::create('grade_job_title', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('grades')->cascadeOnDelete();
            $table->foreignId('job_title_id')->constrained('job_titles')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_job_title');
        Schema::dropIfExists('grades');
    }
};
