<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVolumeToTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('temparature', 5 , 2 )->nullable();
            $table->decimal('volume', 5 , 2 )->nullable();
            $table->decimal('net_weight', 5 , 2 )->nullable();
            $table->text('seal_number')->nullable();
            $table->text('manifest_comments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn('temparature');
            $table->dropColumn('volume');
            $table->dropColumn('net_weight');
            $table->dropColumn('seal_number');
            $table->dropColumn('manifest_comments');
        });
    }
}
