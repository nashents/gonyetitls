<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsLockedToAccountsAndExpensesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('description');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('is_locked');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('is_locked');
        });
    }
}
