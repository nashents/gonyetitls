<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The Sage PROJECT "Depature Location" / "Destination Location" picklists
        // (ENUM custom fields) — their valid values, pulled from Sage. A Gonyeti
        // destination maps to one of these names; the trip push sends it.
        Schema::create('sage_project_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('for_departure')->default(true);
            $table->boolean('for_destination')->default(true);
            $table->timestamps();
        });

        // The picklist value a destination maps to (auto-matched on pull by name,
        // manually overridable). Null = not mapped (nothing sent for that leg).
        Schema::table('destinations', function (Blueprint $table) {
            $table->string('sage_location')->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn('sage_location');
        });
        Schema::dropIfExists('sage_project_locations');
    }
};
