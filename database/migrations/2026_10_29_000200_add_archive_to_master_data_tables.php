<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add the `archive` flag (the same hide-from-list / show-in-Archived pattern the
 * Fleet & Employees modules use) to the master-data tables that lack it, so a Sage
 * re-pull can archive inactive customers/vendors/transporters/products/expenses/
 * stores exactly like it already archives inactive horses/trailers/drivers.
 */
return new class extends Migration
{
    private array $tables = [
        'transporters',
        'customers',
        'vendors',
        'products',
        'expenses',
        'stores',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'archive')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->boolean('archive')->default(0)->index();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'archive')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('archive');
                });
            }
        }
    }
};
