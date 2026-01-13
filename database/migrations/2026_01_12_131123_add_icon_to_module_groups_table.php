<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIconToModuleGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('module_groups', function (Blueprint $table) {
            $table->string('icon')->nullable();
            $table->json('visibility')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('module_groups', function (Blueprint $table) {
           $table->dropColumn('icon');
           $table->dropColumn('visibility');
        });
    }
}
