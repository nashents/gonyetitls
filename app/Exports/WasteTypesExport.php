<?php

namespace App\Exports;

use App\Models\WasteType;
use Maatwebsite\Excel\Concerns\FromCollection;

class WasteTypesExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return WasteType::all();
    }
}
