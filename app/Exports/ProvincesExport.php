<?php

namespace App\Exports;

use App\Models\Province;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProvincesExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Province::all();
    }
}
