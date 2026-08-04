<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultTaxIdTypeToAllowancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('allowances', function (Blueprint $table) {
            // system default allowance (e.g. Trip Bonus) - not deletable
            $table->boolean('default')->default(false)->after('status');
            $table->bigInteger('tax_id')->unsigned()->nullable()->after('default');
            // Inventory, Non Inventory
            $table->string('type')->nullable()->default('Non Inventory')->after('tax_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('allowances', function (Blueprint $table) {
            $table->dropColumn(['default', 'tax_id', 'type']);
        });
    }
}
