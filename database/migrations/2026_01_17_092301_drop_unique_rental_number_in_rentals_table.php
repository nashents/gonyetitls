<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            // Drops UNIQUE index: rentals.rentals_rental_number_unique (your error shows this name)
           $table->dropUnique(['rental_number']);
        });
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            // Re-add uniqueness (rollback)
           $table->dropUnique(['rental_number']);
        });
    }
};