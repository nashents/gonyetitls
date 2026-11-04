<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * shift_id was added without an index, so every "shift has trips / fuel" check in the
 * shift production reports scanned the whole trips/fuels table per shift (504 timeouts).
 */
class AddShiftIdIndexes extends Migration
{
    protected array $tables = ['trips', 'fuels', 'rehandlings'];

    public function up()
    {
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'shift_id') && ! $this->hasIndexOn($table, 'shift_id')) {
                Schema::table($table, fn (Blueprint $t) => $t->index('shift_id'));
            }
        }
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            if ($this->hasIndexOn($table, 'shift_id', "{$table}_shift_id_index")) {
                Schema::table($table, fn (Blueprint $t) => $t->dropIndex("{$table}_shift_id_index"));
            }
        }
    }

    protected function hasIndexOn(string $table, string $column, ?string $name = null): bool
    {
        return collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->contains(fn ($i) => $i->Column_name === $column && ($name === null || $i->Key_name === $name));
    }
}
