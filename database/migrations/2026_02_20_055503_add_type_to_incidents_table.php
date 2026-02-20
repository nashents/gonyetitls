<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToIncidentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incidents', function (Blueprint $table) {
             $table->string('incident_type')->nullable();
             $table->string('category')->nullable();
             $table->boolean('create_trip')->default(false);
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
                $table->dropColumn('incident_type');
                $table->dropColumn('category');
                $table->dropColumn('create_trip');
        });
    }
}
