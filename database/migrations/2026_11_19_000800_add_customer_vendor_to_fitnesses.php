<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reminders (fitnesses) can now be set for a customer or vendor too - e.g. a
 * customer-specific permit - alongside horses, vehicles, trailers and staff.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fitnesses', function (Blueprint $table) {
            if (!Schema::hasColumn('fitnesses', 'customer_id')) {
                $table->bigInteger('customer_id')->unsigned()->nullable()->index();
            }
            if (!Schema::hasColumn('fitnesses', 'vendor_id')) {
                $table->bigInteger('vendor_id')->unsigned()->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fitnesses', function (Blueprint $table) {
            foreach (['customer_id', 'vendor_id'] as $col) {
                if (Schema::hasColumn('fitnesses', $col)) {
                    $table->dropIndex([$col]);
                    $table->dropColumn($col);
                }
            }
        });
    }
};
