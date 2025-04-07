<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Branch;
use Illuminate\Support\Str;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class BranchesDatatables extends LivewireDatatable
{
    public $model = Branch::class;

    public function columns()
    {
    	return [
    		NumberColumn::name('id')->label('ID')->sortBy('id'),
    		Column::name('name')->label('Name'),
    		Column::name('email')->label('Email'),
    		Column::name('phonenumber')->label('Phonenumber'),
    		Column::name('address')->label('Address'),
    		DateColumn::name('created_at')->label('Creation Date')
    	];
    }

    public function render()
    {
        return view('livewire.branches-datatables');
    }
}
