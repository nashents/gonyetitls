<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Teams <-> Departments (many-to-many): a team can sit under several
 * departments and a department can own several teams.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('department_team')) {
            return;
        }

        Schema::create('department_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->unique(['department_id', 'team_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_team');
    }
};
