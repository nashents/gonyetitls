<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChangeLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('change_logs', function (Blueprint $table) {
            $table->id();

            // Global unique identifier across all clients/databases
            $table->string('key')->unique();

            $table->string('title');
            $table->text('description');

            $table->string('module')->nullable();   // e.g. Trips, Policies, HR, Inventory
            $table->string('type')->default('improved'); // added|improved|fixed|removed|security
            $table->string('version')->nullable();  // e.g. 2025.12.29 or v2.9.0

            $table->timestamp('released_at')->nullable();
            $table->boolean('is_published')->default(true);

            // Optional for auditing (who authored in admin UI later)
            $table->unsignedBigInteger('created_by')->nullable();

            // Optional: if you ever want per-tenant logs inside a shared DB
            $table->unsignedBigInteger('company_id')->nullable();

            $table->timestamps();

            $table->index(['released_at']);
            $table->index(['module']);
            $table->index(['type']);
            $table->index(['version']);
            $table->index(['is_published']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('change_logs');
    }
}
