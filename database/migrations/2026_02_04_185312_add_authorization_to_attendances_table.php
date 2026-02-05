<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuthorizationToAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->bigInteger('authorized_by_id')->unsigned()->nullable();
            $table->foreign('authorized_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('authorization')->default('pending');
            $table->text('authorization_comments')->nullable();
            $table->string('authorization_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop FK first (important)
            $table->dropForeign(['authorized_by_id']);

            // Then drop the columns
            $table->dropColumn([
                'authorized_by_id',
                'authorization',
                'authorization_comments',
                'authorization_date',
            ]);
        });
    }
}
