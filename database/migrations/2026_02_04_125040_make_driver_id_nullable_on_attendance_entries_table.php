<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendance_entries', function (Blueprint $table) {
            // Drop FK first
            $table->dropForeign(['driver_id']);

            // Make column nullable
            $table->foreignId('driver_id')->nullable()->change();

            // Re-add FK
            $table->foreign('driver_id')
                ->references('id')
                ->on('drivers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_entries', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);

            // Back to NOT NULL
            $table->foreignId('driver_id')->nullable(false)->change();

            $table->foreign('driver_id')
                ->references('id')
                ->on('drivers')
                ->cascadeOnDelete();
        });
    }
};