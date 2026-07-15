<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvestigationToIncidentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->string('severity')->nullable();
            $table->integer('days_lost')->nullable();
            $table->text('investigation_team')->nullable();
            $table->boolean('scene_visited')->default(0);
            $table->string('scene_visited_by')->nullable();
            $table->date('investigation_date')->nullable();
            $table->text('investigation_findings')->nullable();
            $table->string('investigation_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn('severity');
            $table->dropColumn('days_lost');
            $table->dropColumn('investigation_team');
            $table->dropColumn('scene_visited');
            $table->dropColumn('scene_visited_by');
            $table->dropColumn('investigation_date');
            $table->dropColumn('investigation_findings');
            $table->dropColumn('investigation_status');
        });
    }
}
