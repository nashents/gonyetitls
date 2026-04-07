<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsSerializedToDispatchItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dispatch_items', function (Blueprint $table) {
                $table->decimal('qty_requested', 12, 2)->default(0);
               // snapshot flags from product at transaction time
                $table->boolean('is_trackable')->default(false);
                $table->boolean('is_serialized')->default(false);
                $table->boolean('requires_position')->default(false);
                $table->boolean('requires_fitment')->default(false);

                  // pending, dispatched, partially_fitted, fitted, returned, scrapped
                $table->string('status')->default('pending')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dispatch_items', function (Blueprint $table) {
             $table->dropColumn([
                'qty_requested',
                'is_trackable',
                'is_serialized',
                'requires_position',
                'requires_fitment', 
                'status',
            ]);
        });
    }
}
