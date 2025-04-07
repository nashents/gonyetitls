<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('trip_id')->unsigned()->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->string('measurement')->nullable();
            $table->string('distance')->nullable()->default(0);
            $table->string('offloaded_distance')->nullable()->default(0);
            $table->string('loaded_quantity')->nullable()->default(0);
            $table->string('loaded_litreage')->nullable()->default(0);
            $table->string('loaded_litreage_at_20')->nullable()->default(0);
            $table->string('loaded_weight')->nullable()->default(0);
            $table->string('loaded_rate')->nullable()->default(0);
            $table->string('loaded_freight')->nullable()->default(0);
            $table->string('transporter_loaded_rate')->nullable()->default(0);
            $table->string('transporter_loaded_freight')->nullable()->default(0);
            $table->string('loaded_date')->nullable();
            $table->string('offloaded_quantity')->nullable()->default(0);
            $table->string('offloaded_litreage')->nullable()->default(0);
            $table->string('offloaded_litreage_at_20')->nullable()->default(0);
            $table->string('offloaded_weight')->nullable()->default(0);
            $table->string('offloaded_rate')->nullable()->default(0);
            $table->string('offloaded_freight')->nullable()->default(0);
            $table->string('transporter_offloaded_rate')->nullable()->default(0);
            $table->string('transporter_offloaded_freight')->nullable()->default(0);
            $table->string('offloaded_date')->nullable();
            $table->string('weight_loss')->nullable();
            $table->string('allowable_weight_loss')->nullable();
            $table->string('chargeable_weight_loss')->nullable();
            $table->string('quantity_loss')->nullable();
            $table->string('chargeable_quantity_loss')->nullable();
            $table->string('allowable_quantity_loss')->nullable();
            $table->string('litreage_loss')->nullable();
            $table->string('chargeable_litreage_loss')->nullable();
            $table->string('allowable_litreage_loss')->nullable();
            $table->string('litreage_at_20_loss')->nullable();
            $table->string('chargeable_litreage_at_20_loss')->nullable();
            $table->string('allowable_litreage_at_20_loss')->nullable();
            $table->text('comments')->nullable();
            $table->boolean('status')->nullable()->default(0);
            $table->boolean('updated_from_offloading_points')->nullable()->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_notes');
    }
}
