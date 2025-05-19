<?php

namespace App\Imports;

use App\Models\Work;
use Maatwebsite\Excel\Concerns\ToModel;

class WorksImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Work([
            //
        ]);
    }
}
