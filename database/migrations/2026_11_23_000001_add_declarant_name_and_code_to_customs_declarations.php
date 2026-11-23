<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A declarant who isn't one of our staff (e.g. an outside clearing agent's
 * declarant) can be recorded by name, and any declarant's customs
 * registration / declarant code can be captured. declarant_id still holds
 * the staff member when the declarant is one of ours.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customs_declarations', function (Blueprint $table) {
            $table->string('declarant_name')->nullable()->after('declarant_id');
            $table->string('declarant_code')->nullable()->after('declarant_name');
        });
    }

    public function down(): void
    {
        Schema::table('customs_declarations', function (Blueprint $table) {
            $table->dropColumn(['declarant_name', 'declarant_code']);
        });
    }
};
