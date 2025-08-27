<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeQualificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('qualification_id')->constrained('qualifications')->cascadeOnDelete();

            // What the employee holds
            $table->unsignedTinyInteger('level')->nullable();      // achieved level
            $table->date('date_awarded')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('certificate_path')->nullable();        // storage path for file
            $table->enum('status', ['pending','verified','rejected'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
            $table->unique(['employee_id','qualification_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_qualifications');
    }
}
