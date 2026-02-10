<?php

namespace App\Exports;

use App\Models\WasteCollection;
use Maatwebsite\Excel\Concerns\FromCollection;

class WasteCollectionsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return WasteCollection::all();
    }
}
