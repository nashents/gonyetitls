<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Links an expense to its non-inventory billable Product (1:1). An expense
     * created in Gonyeti auto-creates the product; a non-inventory Sage item
     * pulled into Products also gets a matching expense — both share this link.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('product_id');
        });
    }
};
