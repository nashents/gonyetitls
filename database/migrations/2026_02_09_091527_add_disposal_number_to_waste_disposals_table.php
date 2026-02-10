<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisposalNumberToWasteDisposalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('waste_disposals', function (Blueprint $table) {
            $table->string('waste_disposal_number')->nullable(); 
            $table->boolean('inspection')->nullable()->default(False); 
            $table->foreignId('inspection_by_id')->nullable()->constrained('users')->cascadeOnDelete();
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
        Schema::table('waste_disposals', function (Blueprint $table) {
            $table->dropForeign(['inspection_by_id']);
          

            // Then drop the columns
            $table->dropColumn([
                'waste_disposal_number',
                'inspection',
                'inspection_by_id',
                'inspection_comments',
                'receiving_date',
            ]);
        });
    }
}
