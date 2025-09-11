<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrailerIdToBreakdownsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('breakdowns', function (Blueprint $table) {
            $table->bigInteger('trailer_id')->nullable()->unsigned();
            $table->bigInteger('vehicle_id')->nullable()->unsigned();
            $table->bigInteger('closed_by_id')->nullable()->unsigned();
            $table->string('closed_on')->nullable();
            $table->text('closed_comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('breakdowns', function (Blueprint $table) {
             $table->dropColumn('trailer_id');
            $table->dropColumn('vehicle_id');
            $table->dropColumn('closed_on');
            $table->dropColumn('closed_by_id');
            $table->dropColumn('closed_comments');
        });
    }
}
