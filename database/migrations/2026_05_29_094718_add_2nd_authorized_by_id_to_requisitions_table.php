<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Add2ndAuthorizedByIdToRequisitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requisitions', function (Blueprint $table) {
            $table->unsignedBigInteger('second_authorized_by_id')->nullable();
            $table->timestamp('second_authorization_date')->nullable();
            $table->text('second_authorization_comments')->nullable();
            $table->integer('authorization_stage')->default(0); // 0 = pending // 1 = first approval done // 2 = fully approved
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requisitions', function (Blueprint $table) {
            $table->dropColumn('second_authorized_by_id');
            $table->dropColumn('second_authorization_date');
            $table->dropColumn('second_authorization_comments');
            $table->dropColumn('authorization_stage');
        });
    }
}
