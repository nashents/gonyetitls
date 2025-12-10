<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuthorizationToTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->bigInteger('authorized_by_id')->unsigned()->nullable();
            $table->foreign('authorized_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('authorization')->default('pending');
            $table->text('authorization_comments')->nullable();
            $table->string('authorization_date')->nullable();
            $table->text('description')->nullable();
            $table->string('department')->nullable();
            $table->string('dispatch_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->dropForeign(['authorized_by_id']);
            $table->dropColumn('authorized_by_id');
            $table->dropColumn('authorization')->default('pending');
            $table->dropColumn('authorization_comments')->nullable();
            $table->dropColumn('authorization_date')->nullable();
            $table->dropColumn('description')->nullable();
            $table->dropColumn('department')->nullable();
            $table->dropColumn('dispatch_number')->nullable();
        });
    }
}
