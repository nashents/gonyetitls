<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsLockedToSeededLookupTables extends Migration
{
    /**
     * Tables shipped with seeded "system lookup" rows that the app's backend
     * looks up by hardcoded name/code, or that cascade-delete other data.
     * accounts/expenses already got this column in an earlier migration.
     */
    protected array $tables = [
        'deductions',
        'account_types',
        'departments',
        'leave_types',
        'trip_types',
        'checklist_categories',
        'payment_methods',
        'folders',
        'loss_categories',
        'loss_groups',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->boolean('is_locked')->default(false);
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
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('is_locked');
            });
        }
    }
}
