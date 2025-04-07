<?php

namespace App\Exports;

use App\Models\CashFlow;
use Maatwebsite\Excel\Concerns\FromCollection;

class CashFlowsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return CashFlow::all();
    }
}
