<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SHEQ lookup lists that had only a name get a description, so users can
     * read what each entry means (Loss Cause Categories / Groups / Causes,
     * Monitoring Parameters, Training Items / Departments).
     */
    private array $tables = [
        'loss_categories', 'loss_groups', 'losses',
        'sheq_monitoring_parameters', 'training_items', 'training_departments',
    ];

    public function up()
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasColumn($table, 'description')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->text('description')->nullable()->after('name');
                });
            }
        }
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'description')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('description');
                });
            }
        }
    }
};
