<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsCompletedToRequisitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requisitions', function (Blueprint $table) {
             $table->boolean('is_completed')->default(False);
             $table->bigInteger('completed_by_id')->nullable()->unsigned();
             $table->string('completed_on')->nullable();
             $table->text('completed_comments')->nullable();
             $table->bigInteger('paid_by_id')->nullable()->unsigned();
             $table->string('paid_on')->nullable();
             $table->text('paid_comments')->nullable();
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
             $table->dropColumn('is_completed');
             $table->dropColumn('completed_by_id');
             $table->dropColumn('completed_on');
             $table->dropColumn('completed_comments');
             $table->dropColumn('paid_by_id');
             $table->dropColumn('paid_on');
             $table->dropColumn('paid_comments');
        });
    }
}
