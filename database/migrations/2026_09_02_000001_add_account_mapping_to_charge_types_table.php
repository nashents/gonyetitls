<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountMappingToChargeTypesTable extends Migration
{
    public function up()
    {
        Schema::table('charge_types', function (Blueprint $table) {
            $table->unsignedBigInteger('revenue_account_id')->nullable()->after('is_locked');
            $table->foreign('revenue_account_id')->references('id')->on('accounts')->onDelete('set null');

            $table->unsignedBigInteger('expense_account_id')->nullable()->after('revenue_account_id');
            $table->foreign('expense_account_id')->references('id')->on('accounts')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('charge_types', function (Blueprint $table) {
            $table->dropForeign(['revenue_account_id']);
            $table->dropForeign(['expense_account_id']);
            $table->dropColumn(['revenue_account_id', 'expense_account_id']);
        });
    }
}
