<?php

namespace App\Imports;

use App\Models\Rack;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class RacksImport implements ToCollection, SkipsEmptyRows, WithLimit, 
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

            $rack = Rack::where('rack_number',$row['rack_number'])->get()->first();

            if (isset($rack)) {
              
                $rack->name = $row['name'];
                $rack->rack_number = $row['rack_number'];
                $rack->description = $row['description'];
                $rack->update();
              
            } else {
                $rack = new Rack;
                $rack->user_id     = Auth::user()->id;
                $rack->name = $row['name'];
                $rack->rack_number = $row['rack_number'];
                $rack->description = $row['description'];
                $rack->save();
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
