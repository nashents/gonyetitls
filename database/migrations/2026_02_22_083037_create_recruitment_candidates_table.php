<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecruitmentCandidatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruitment_candidates', function (Blueprint $table) {
             $table->id();

            // Multi-tenant / company scope (adjust table name if needed)
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();

            // who captured the candidate
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // if the candidate becomes an employee, link here
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();

            $table->foreignId('application_id')->nullable();

            $table->date('applied_at')->nullable();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            $table->date('dob')->nullable();
            $table->unsignedTinyInteger('age')->nullable(); // optional cache (can be computed)

            // IDs / license
            $table->string('national_id')->nullable();
            $table->string('drivers_license_number')->nullable();

            // contact
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // experience
            $table->unsignedTinyInteger('years_experience')->nullable();

            // Source: excel_import, referral, walk_in, etc
            $table->string('source')->nullable()->default('manual');

            // Pipeline status (simple string enum style)
            // applied, screened, road_test, engaged, declined, contracted, hired
            $table->string('status')->default('applied');

            // Quick summary fields (optional, mirrors Excel feel)
            $table->string('screening_impression')->nullable(); // IMPRESSED / UNIMPRESSED
            $table->string('next_step')->nullable();            // ROAD_TEST / DECLINE / ENGAGE etc
            $table->text('notes')->nullable();

            // Optional aggregates (avoid formulas like #DIV/0!)
            $table->unsignedTinyInteger('screening_score')->nullable(); // 0..100
            $table->unsignedTinyInteger('road_test_score')->nullable(); // 0..100
            $table->unsignedTinyInteger('overall_score')->nullable();   // 0..100

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['last_name', 'first_name']);
            $table->index(['national_id']);
            $table->index(['drivers_license_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruitment_candidates');
    }
}
