<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customs registration / declarant code for staff who lodge customs entries.
 * Picking an employee as a customs declaration's declarant pulls it in
 * (copied onto the declaration, so later changes don't rewrite history).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('employees', 'customs_code')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('customs_code', 100)->nullable()->after('idnumber');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('employees', 'customs_code')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('customs_code');
            });
        }
    }
};
