<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultsToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
              $table->bigInteger('transporter_id')->nullable()->unsigned();
              $table->bigInteger('loading_point_id')->nullable()->unsigned();
              $table->bigInteger('offloading_point_id')->nullable()->unsigned();
              $table->bigInteger('customer_id')->nullable()->unsigned();
              $table->bigInteger('cargo_id')->nullable()->unsigned();
              $table->bigInteger('from')->nullable()->unsigned();
              $table->bigInteger('to')->nullable()->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('transporter_id');
            $table->dropColumn('loading_point_id');
            $table->dropColumn('offloading_point_id');
            $table->dropColumn('customer_id');
            $table->dropColumn('cargo_id');
            $table->dropColumn('from');
            $table->dropColumn('to');
        });
    }
}
