<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComponentSlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('component_slots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('code')->nullable();

            // tyre, battery, spring, shock, brake_chamber, spare_wheel, general_component
            $table->string('category')->index();

            // horse, trailer, vehicle, general
            $table->string('asset_type')->nullable()->index();

            // optional slot metadata
            $table->string('axle')->nullable()->index();
            $table->string('side')->nullable()->index(); // left, right, center
            $table->unsignedInteger('sequence_no')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(true);

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['category', 'asset_type', 'name'], 'component_slots_unique_name_per_scope');
            $table->unique(['category', 'asset_type', 'code'], 'component_slots_unique_code_per_scope');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('component_slots');
    }
}
