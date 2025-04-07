<?php

namespace App\Exports;

use App\Models\Horse;
use Maatwebsite\Excel\Concerns\FromCollection;

class HorsesPerformanceExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Horse::all();
    }
}
