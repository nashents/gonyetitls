<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHorseIdToCategoryChecklistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
 public function up()
    {
        Schema::table('category_checklists', function (Blueprint $table) {
            $table->foreignId('horse_id')->nullable()
                ->constrained('horses')->nullOnDelete();

            $table->foreignId('trailer_id')->nullable()
                ->constrained('trailers')->nullOnDelete();

            $table->foreignId('vehicle_id')->nullable()
                ->constrained('vehicles')->nullOnDelete();

            $table->text('condition')->nullable();

        });
    }

    public function down()
    {
        Schema::table('category_checklists', function (Blueprint $table) {
            $table->dropForeign(['horse_id']);
            $table->dropForeign(['trailer_id']);
            $table->dropForeign(['vehicle_id']);

            $table->dropColumn(['horse_id','trailer_id','vehicle_id','condition']);

            // If you added a named CHECK, drop it here with DB::statement(...)
        });
    }
}
