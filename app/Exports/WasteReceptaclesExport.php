<?php

namespace App\Exports;

use App\Models\WasteReceptacle;
use Maatwebsite\Excel\Concerns\FromCollection;

class WasteReceptaclesExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return WasteReceptacle::all();
    }
}
