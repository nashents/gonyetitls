<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional free-text reference on a fuel order (pump slip, supplier ref,
 * etc). Uniqueness is enforced by the fuel order forms, not the column,
 * so blank references never collide.
 */
class AddReferenceToFuelsTable extends Migration
{
    public function up()
    {
        Schema::table('fuels', function (Blueprint $table) {
            $table->string('reference')->nullable()->after('order_number')->index();
        });
    }

    public function down()
    {
        Schema::table('fuels', function (Blueprint $table) {
            $table->dropIndex(['reference']);
            $table->dropColumn('reference');
        });
    }
}
