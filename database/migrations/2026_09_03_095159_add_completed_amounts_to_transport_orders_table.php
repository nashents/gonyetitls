<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Actual amounts pushed/delivered when a transport order is marked Completed,
     * kept alongside its target weight/litreage/quantity (which are NOT overwritten).
     */
    public function up(): void
    {
        Schema::table('transport_orders', function (Blueprint $table) {
            $table->decimal('completed_weight', 18, 2)->nullable()->after('weight');
            $table->decimal('completed_litreage', 18, 2)->nullable()->after('litreage');
            $table->decimal('completed_quantity', 18, 2)->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('transport_orders', function (Blueprint $table) {
            $table->dropColumn(['completed_weight', 'completed_litreage', 'completed_quantity']);
        });
    }
};
