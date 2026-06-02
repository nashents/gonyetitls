<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterFreightDecimalOnDealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            ALTER TABLE deals
            MODIFY freight DECIMAL(18,2) NULL,
            MODIFY rate DECIMAL(18,2) NULL,
            MODIFY weight DECIMAL(18,2) NULL,
            MODIFY litreage DECIMAL(18,2) NULL,
            MODIFY quantity DECIMAL(18,2) NULL
        ");
    }

    public function down()
    {
        DB::statement("
            ALTER TABLE deals
            MODIFY freight DECIMAL(8,2) NULL,
            MODIFY rate DECIMAL(8,2) NULL
            MODIFY weight DECIMAL(8,2) NULL,
            MODIFY litreage DECIMAL(8,2) NULL,
            MODIFY quantity DECIMAL(8,2) NULL
        ");
    }
}
