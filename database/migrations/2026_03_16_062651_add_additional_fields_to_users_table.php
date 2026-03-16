<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_login_ip', 45)->nullable();   // 45 chars covers IPv6
            $table->string('last_login_city')->nullable();
            $table->string('last_login_country_code', 2)->nullable();
            $table->string('last_login_country')->nullable();
            $table->decimal('last_login_lat', 10, 7)->nullable();
            $table->decimal('last_login_lng', 10, 7)->nullable();
            $table->string('last_login_address')->nullable(); // reverse-geocoded human label
            $table->string('last_login_accuracy')->nullable(); // metres radius from browser
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_login_ip');
            $table->dropColumn('last_login_city');
            $table->dropColumn('last_login_country_code');
            $table->dropColumn('last_login_country');
            $table->dropColumn('last_login_lat');
            $table->dropColumn('last_login_lng');
            $table->dropColumn('last_login_address');
            $table->dropColumn('last_login_accuracy');
        });
    }
}
