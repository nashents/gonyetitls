<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsSerializedToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_trackable')->default(false)->after('name');
            $table->boolean('is_serialized')->default(false)->after('is_trackable');
            $table->boolean('requires_position')->default(false)->after('is_serialized');
            $table->boolean('requires_fitment')->default(false)->after('requires_position');

            // single_unit, quantity_split, bulk_issue
            $table->string('fitment_mode')->nullable()->after('requires_fitment');

            // tyre, battery, spring, shock, brake_chamber, spare_wheel, general_component
            $table->string('position_category')->nullable()->after('fitment_mode')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
           $table->dropColumn([
                'is_trackable',
                'is_serialized',
                'requires_position',
                'requires_fitment',
                'fitment_mode',
                'position_category',
            ]);
        });
    }
}
