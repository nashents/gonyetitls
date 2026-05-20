<?php

namespace App\Imports;

use App\Models\Bill;
use Maatwebsite\Excel\Concerns\ToModel;

class BillsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Bill([
            //
        ]);
    }
}
