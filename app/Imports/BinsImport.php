<?php

namespace App\Imports;

use App\Models\Bin;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BinsImport implements ToCollection,
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading
{
    use Importable, SkipsErrors;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
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




    public function chunkSize(): int
    {
        return 1000;
    }
}
