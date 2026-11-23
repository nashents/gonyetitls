<?php

use Database\Seeders\ContainerStageSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Container lifecycle stages move from the ShippingContainer::LIFECYCLE_STAGES
 * constant to a table so users can add custom stages (Freight > Master >
 * Container Stages). The 11 system stages are seeded with their existing
 * codes, so container statuses, milestones and Charge Type clock milestones
 * keep matching.
 */
class CreateContainerStagesTable extends Migration
{
    public function up()
    {
        Schema::create('container_stages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        (new ContainerStageSeeder)->run();
    }

    public function down()
    {
        Schema::dropIfExists('container_stages');
    }
}
