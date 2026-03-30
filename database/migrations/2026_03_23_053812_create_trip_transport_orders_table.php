<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripTransportOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trip_transport_orders', function (Blueprint $table) {
            $table->id();
            // Relationships
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transport_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('units_of_measure_id')->nullable()->constrained();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            // Allocation (what portion of the order is assigned to THIS trip)
            $table->decimal('allocated_quantity', 15, 3)->nullable();
            $table->decimal('allocated_litreage', 15, 3)->nullable();
            $table->decimal('allocated_weight', 15, 3)->nullable();
            $table->decimal('allocated_volume', 15, 3)->nullable();
            $table->decimal('short_quantity', 15, 3)->nullable();

            // Delivery (what was actually delivered on THIS trip)
            $table->decimal('delivered_quantity', 15, 3)->nullable();
            $table->decimal('delivered_weight', 15, 3)->nullable();
            $table->decimal('delivered_volume', 15, 3)->nullable();
            $table->decimal('delivered_litreage', 15, 3)->nullable();

            // Sequence (for multi-drop / route ordering)
            $table->unsignedInteger('sequence_no')->default(1);

            // Status tracking
            $table->string('status')->default('pending');
            // Suggested values: pending, loaded, in_transit, delivered, partial, cancelled

            // Optional notes
            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['trip_id']);
            $table->index(['transport_order_id']);
            $table->index(['status']);

            // Prevent duplicate linking of same TO to same Trip
            $table->unique(['trip_id', 'transport_order_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trip_transport_orders');
    }
}
