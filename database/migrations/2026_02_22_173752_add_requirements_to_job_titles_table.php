<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequirementsToJobTitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_titles', function (Blueprint $table) {
           $table->text('duties')->nullable();
           $table->text('requirements')->nullable();
           $table->text('instructions')->nullable();
           $table->bigInteger('rank_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_titles', function (Blueprint $table) {
            $table->dropColumn('duties');
            $table->dropColumn('requirements');
            $table->dropColumn('instructions');
            $table->dropColumn('rank_id');
        });
    }
}
