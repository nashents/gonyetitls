<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Liquid cargo readings beside the meter readings (loaded_/offloaded_litreage[_at_20]):
 * tank dips at loading + offloading, and the customer's shoretank reading at offloading.
 */
class AddDipAndShoretankReadingsToDeliveryNotesTable extends Migration
{
    public function up()
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->decimal('loaded_dip_litreage', 15, 3)->nullable();
            $table->decimal('loaded_dip_litreage_at_20', 15, 3)->nullable();
            $table->decimal('offloaded_dip_litreage', 15, 3)->nullable();
            $table->decimal('offloaded_dip_litreage_at_20', 15, 3)->nullable();
            $table->decimal('shoretank_litreage', 15, 3)->nullable();
            $table->decimal('shoretank_litreage_at_20', 15, 3)->nullable();
        });
    }

    public function down()
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropColumn([
                'loaded_dip_litreage', 'loaded_dip_litreage_at_20',
                'offloaded_dip_litreage', 'offloaded_dip_litreage_at_20',
                'shoretank_litreage', 'shoretank_litreage_at_20',
            ]);
        });
    }
}
