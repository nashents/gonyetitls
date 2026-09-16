<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Traceability from a debit note back to the goods return that raised it
 * (GoodsReturnedReversalService creates one debit note per approved return),
 * and department so the return list/reporting screens can filter debit
 * notes the same way GRVs are filtered.
 */
class AddDepartmentAndGoodsReturnedIdToDebitNotesTable extends Migration
{
    public function up()
    {
        Schema::table('debit_notes', function (Blueprint $table) {
            $table->string('department')->nullable();
            $table->bigInteger('goods_returned_id')->unsigned()->nullable();
            $table->foreign('goods_returned_id')->references('id')->on('goods_returneds')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('debit_notes', function (Blueprint $table) {
            $table->dropForeign(['goods_returned_id']);
            $table->dropColumn(['department', 'goods_returned_id']);
        });
    }
}
