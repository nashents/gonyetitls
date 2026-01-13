<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerIdToDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('drivers', function (Blueprint $table) {
              $table->bigInteger('customer_id')->nullable()->unsigned();
              $table->string('name')->nullable();
              $table->string('surname')->nullable();
              $table->string('email')->nullable();
              $table->string('phonenumber')->nullable();
              $table->string('address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('customer_id');
            $table->dropColumn('name');
            $table->dropColumn('surname');
            $table->dropColumn('email');
            $table->dropColumn('phonenumber');
            $table->dropColumn('address');
        });
    }
}
