<?php

namespace App\Exports;

use App\Models\Fuel;
use Maatwebsite\Excel\Concerns\FromCollection;

class TrailerFuelExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Fuel::all();
    }
}
