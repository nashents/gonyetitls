<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPeriodToRecoveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recoveries', function (Blueprint $table) {
            $table->string('period')->nullable();
            $table->decimal('payment_per_month', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recoveries', function (Blueprint $table) {
            $table->dropColumn('period');
            $table->dropColumn('payment_per_month');
        });
    }
}
