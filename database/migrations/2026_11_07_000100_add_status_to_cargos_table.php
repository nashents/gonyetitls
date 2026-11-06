<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Active / inactive cargos. Every existing cargo starts active; users switch
 * off the ones they don't carry (Cargos list or edit form). The Loss Rates
 * table and its export only show active cargos.
 */
class AddStatusToCargosTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('cargos', 'status')) {
            Schema::table('cargos', function (Blueprint $table) {
                $table->boolean('status')->default(true)->after('risk');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('cargos', 'status')) {
            Schema::table('cargos', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
}
