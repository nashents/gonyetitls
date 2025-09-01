<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete();
                $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
                $table->date('attendance_date');
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
                $table->time('start_time')->nullable();             // optional
                $table->time('end_time')->nullable();               // optional
                $table->decimal('hours', 5, 2)->nullable();         // optional
                $table->string('source')->default('manual');        // manual/import
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('users');
                $table->foreignId('updated_by')->nullable()->constrained('users');
                $table->timestamps();

               
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_entries');
    }
}
