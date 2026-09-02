<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToVendorsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('vendors', 'company_id')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->bigInteger('company_id')->unsigned()->nullable()->after('vendor_type_id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('vendors', 'company_id')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }
    }
}
