<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Demo data that an is_admin user loads on a live instance (Freight >
 * Demo Data) and later rolls back. Every row created while loading is
 * recorded in demo_data_records so rollback removes exactly those rows.
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('demo_data_batches')) {
            Schema::create('demo_data_batches', function (Blueprint $table) {
                $table->id();
                $table->string('module');                     // e.g. freight
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('status')->default('loaded');  // loaded / rolled_back
                $table->json('summary')->nullable();          // counts of what was created
                $table->json('rollback_summary')->nullable();
                $table->dateTime('loaded_at')->nullable();
                $table->dateTime('rolled_back_at')->nullable();
                $table->unsignedBigInteger('rolled_back_by')->nullable();
                $table->timestamps();
                $table->index(['module', 'status']);
            });
        }

        if (!Schema::hasTable('demo_data_records')) {
            Schema::create('demo_data_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('demo_data_batch_id')->index();
                $table->string('table_name');
                $table->unsignedBigInteger('record_id');
                $table->string('model_type')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index(['table_name', 'record_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('demo_data_records');
        Schema::dropIfExists('demo_data_batches');
    }
};
