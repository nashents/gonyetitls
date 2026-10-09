<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxableToAllowancesTable extends Migration
{
    /**
     * Whether an allowance counts towards PAYE. Non-taxable allowances still
     * make up gross pay, they're just left out of the income PAYE is worked
     * on. Defaults to taxable so existing payroll results don't change.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('allowances', function (Blueprint $table) {
            $table->boolean('taxable')->default(true)->after('status');
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
            $table->dropColumn('taxable');
        });
    }
}
