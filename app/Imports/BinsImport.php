<?php

namespace App\Imports;

use App\Models\Bin;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BinsImport implements ToCollection, SkipsEmptyRows, WithLimit, 
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts
{
    use Importable, SkipsErrors;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

     public function limit(): int
    {
        return 2500; // Import only the first 100 rows
    }

    public function collection(Collection $rows)
    {


       foreach($rows as $row){
        if($row->filter()->isNotEmpty()){

            $bin = Bin::where('bin_number',$row['bin_number'])->get()->first();

            if (isset($bin)) {
              
                $bin->name = $row['name'];
                $bin->bin_number = $row['bin_number'];
                $bin->description = $row['description'];
                $bin->update();
              
            } else {
                $bin = new Bin;
                $bin->user_id     = Auth::user()->id;
                $bin->name = $row['name'];
                $bin->bin_number = $row['bin_number'];
                $bin->description = $row['description'];
                $bin->save();
            }
            
          
            
    }
       }
    }

    public function rules(): array{
        return[
            // '*.name' => ['required','unique:countries,name,NULL,id,deleted_at,NULL'],
        ];
    }



    public function batchSize(): int
    {
        return 10;
    }

    public function chunkSize(): int
    {
        return 10;
    }
}
