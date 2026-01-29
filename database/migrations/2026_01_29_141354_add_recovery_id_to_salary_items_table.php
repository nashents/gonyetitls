<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecoveryIdToSalaryItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary_items', function (Blueprint $table) {
               $table->bigInteger('recovery_id')->unsigned()->nullable();
               $table->string('movement')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salary_items', function (Blueprint $table) {
            $table->dropColumn('recovery_id');
            $table->dropColumn('movement');
        });
    }
}
