<?php

namespace App\Exports;

use App\Models\Consignee;
use Maatwebsite\Excel\Concerns\FromCollection;

class ConsigneesExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Consignee::all();
    }
}
