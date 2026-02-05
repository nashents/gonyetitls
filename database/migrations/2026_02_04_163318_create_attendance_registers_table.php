<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_registers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('attendance_id')->nullable()->constrained('attendances')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->foreignId('updated_by_id')->nullable()->constrained('users');
                $table->foreignId('employee_id')->nullable()->constrained('employees')->cascadeOnDelete();
                $table->date('date');
                $table->enum('status', [
                    'present',          // worked
                    'off',              // scheduled off/rest day
                    'annual_leave',
                    'sick_leave',
                    'unpaid_leave',
                    'training',
                    'public_holiday',
                    'suspension',
                    'absent'            // no-show
                ])->default('present');
                $table->enum('shift', ['day','night'])->nullable(); // optional
                $table->time('checkin')->nullable();             // optional
                $table->time('checkout')->nullable();               // optional
                $table->decimal('hours', 5, 2)->nullable();         // optional
                $table->string('source')->default('manual');        // manual/import
                $table->text('notes')->nullable();
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
        Schema::dropIfExists('attendance_registers');
    }
}
