<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWasteCollectionNumberToWasteCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('waste_collections', function (Blueprint $table) {
            $table->string('waste_collection_number')->nullable(); 
            $table->boolean('inspection')->nullable()->default(False); 
            $table->foreignId('inspection_by_id')->nullable()->constrained('users')->cascadeOnDelete();
             $table->bigInteger('booking_id')->unsigned()->nullable();
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->bigInteger('ticket_id')->unsigned()->nullable();
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->text('inspection_comments')->nullable(); 
            $table->date('receiving_date')->nullable(); 
           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('waste_collections', function (Blueprint $table) {
          
            $table->dropForeign(['inspection_by_id']);
            $table->dropForeign(['booking_id']);
            $table->dropForeign(['ticket_id']);

            // Then drop the columns
            $table->dropColumn([
                'waste_collection_number',
                'inspection',
                'inspection_by_id',
                'booking_id',
                'ticket_id',
                'inspection_comments',
                'receiving_date',
            ]);
        });
    }
}
