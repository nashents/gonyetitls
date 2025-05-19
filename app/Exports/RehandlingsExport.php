<?php

namespace App\Exports;

use App\Models\Rehandling;
use Maatwebsite\Excel\Concerns\FromCollection;

class RehandlingsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Rehandling::all();
    }
}
