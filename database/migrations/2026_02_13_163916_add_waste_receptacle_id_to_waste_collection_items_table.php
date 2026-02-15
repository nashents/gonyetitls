<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWasteReceptacleIdToWasteCollectionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('waste_collection_items', function (Blueprint $table) {
             $table->bigInteger('waste_receptacle_id')->nullable()->unsigned();
             $table->decimal('balance', 15, 2)->nullable()->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('waste_collection_items', function (Blueprint $table) {
                $table->dropColumn('waste_receptacle_id');
                $table->dropColumn('balance');
        });
    }
}
