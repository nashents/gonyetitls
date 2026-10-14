<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The vendor's own invoice number for a captured bill (shown on the
     * ZIMRA sales & purchases schedule).
     */
    public function up()
    {
        if (!Schema::hasColumn('bills', 'invoice_number')) {
            Schema::table('bills', function (Blueprint $table) {
                $table->string('invoice_number')->nullable()->after('due_date');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('bills', 'invoice_number')) {
            Schema::table('bills', function (Blueprint $table) {
                $table->dropColumn('invoice_number');
            });
        }
    }
};
