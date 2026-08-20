<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseTypeToContainersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // The original create_containers_table migration has declared this
        // column since the first commit in this repo, but some deployed
        // databases never actually got it (their `migrations` table marks
        // that migration as already run without the column existing). Guard
        // with hasColumn so this is a safe no-op wherever it already exists.
        if (!Schema::hasColumn('containers', 'purchase_type')) {
            Schema::table('containers', function (Blueprint $table) {
                $table->string('purchase_type')->default('Bulk Buy')->after('capacity');
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
        //
    }
}
