<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sage Intacct item "GL Group" (GLGROUP) round-tripped onto the product, so a
 * pulled item keeps its exact GL posting group and a later push re-uses it.
 * Net-new products fall back to the configured default (see config sageintacct.item).
 */
class AddGlGroupToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'gl_group')) {
                $table->string('gl_group')->nullable()->after('type');
            }
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'gl_group')) {
                $table->dropColumn('gl_group');
            }
        });
    }
}
