<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the draft -> submit -> approve/reject workflow to goods returns,
 * mirroring goods_receiveds.authorization. Nullable (unlike the GRV column)
 * because a return has a real pre-submission draft stage: null while a
 * draft is still being edited, 'pending' once submitted for approval,
 * 'approved'/'rejected' after a decision.
 */
class AddAuthorizationToGoodsReturnedsTable extends Migration
{
    public function up()
    {
        Schema::table('goods_returneds', function (Blueprint $table) {
            $table->bigInteger('authorized_by_id')->unsigned()->nullable();
            $table->foreign('authorized_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('authorization')->nullable();
            $table->text('authorization_comments')->nullable();
            $table->string('authorization_date')->nullable();

            $table->bigInteger('debit_note_id')->unsigned()->nullable();
            $table->foreign('debit_note_id')->references('id')->on('debit_notes')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('goods_returneds', function (Blueprint $table) {
            $table->dropForeign(['authorized_by_id']);
            $table->dropForeign(['debit_note_id']);
            $table->dropColumn(['authorized_by_id', 'authorization', 'authorization_comments', 'authorization_date', 'debit_note_id']);
        });
    }
}
