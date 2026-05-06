<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryToFuelRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuel_requests', function (Blueprint $table) {
              $table->string('category')->nullable();
              $table->bigInteger('authorized_by_id')->unsigned()->nullable();
              $table->string('authorization')->default('pending');
              $table->string('authorization_date')->nullable();
              $table->string('authorization_comments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fuel_requests', function (Blueprint $table) {
            $table->dropColumn(
                [
                   'category',
                   'authorization',
                   'authorization_date',
                   'authorization_comments',
                   'authorized_by_id',
                ]);
        });
    }
}
