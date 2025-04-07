<?php

namespace App\Http\Livewire\Branches;

use App\Models\Branch;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;

class BranchesTable extends LivewireDatatable
{
    public $model = Branch::class;

    public function columns()
    {
        return [
        // NumberColumn::name('id')->label('ID')->sortBy('id'),
        Column::name('name')->label('Name'),
        Column::name('email')->label('Email'),
        // Column::name('phonenumber')->label('Phonenumber'),
        // Column::name('address')->label('Address'),
        // DateColumn::name('created_at')->label('Creation Date'),
        ];
    }
}
