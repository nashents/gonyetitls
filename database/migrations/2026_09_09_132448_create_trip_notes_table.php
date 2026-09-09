<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripNotesTable extends Migration
{
    /**
     * Free-text trip comments/updates — deliberately separate from
     * trip_statuses (App\Models\TripStatus), which stays reserved for
     * formal status changes (Scheduled/Loaded/Offloaded/etc). This is the
     * running ops conversation: "SHUNT", "to arrive by...", "notify on
     * arrival", etc, optionally tagged with a smart-comment task type and a
     * position snapshot at the time it was posted.
     */
    public function up()
    {
        Schema::create('trip_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->string('task_type')->nullable();
            $table->text('body');
            $table->string('area')->nullable();
            $table->string('by_time')->nullable();
            $table->string('notify')->nullable();
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();
            $table->string('location_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['trip_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('trip_notes');
    }
}
