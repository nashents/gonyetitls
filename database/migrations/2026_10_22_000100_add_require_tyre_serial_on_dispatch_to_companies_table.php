<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequireTyreSerialOnDispatchToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('companies', 'require_tyre_serial_on_dispatch')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->boolean('require_tyre_serial_on_dispatch')->default(true);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('require_tyre_serial_on_dispatch');
        });
    }
}
