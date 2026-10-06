<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Generic all-columns dump of a model's table, used by the data backup for
 * modules that have no dedicated export (or only a headerless ::all() stub).
 * Hidden attributes (passwords, tokens) are left out.
 */
class ModelBackupExport implements FromQuery, WithHeadings, WithMapping
{
    protected Model $model;
    protected array $columns;

    public function __construct(string $modelClass)
    {
        $this->model = new $modelClass;
        $this->columns = array_values(array_diff(
            Schema::getColumnListing($this->model->getTable()),
            $this->model->getHidden()
        ));
    }

    public function query()
    {
        return $this->model->newQuery()->select($this->columns)->orderBy($this->model->getKeyName());
    }

    public function headings(): array
    {
        return $this->columns;
    }

    public function map($row): array
    {
        $attributes = $row->getAttributes();

        return array_map(fn ($column) => $attributes[$column] ?? null, $this->columns);
    }
}
