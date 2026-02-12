<?php

namespace App\Imports;

use App\Models\Bin;
use App\Models\Product;
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

            $product = Product::where('identification_number', $row->get('part_number'))->first();

            $bin = Bin::firstOrNew(['bin_number' => $row->get('bin_number')]);

            if (!$bin->exists) {
                $bin->user_id = Auth::id();
            }

            $bin->product_id        = $product?->id ?? null;
            $bin->name        = $row->get('name');
            $bin->part_number = $row->get('part_number');
            $bin->unit_of_measure = $row->get('unit_of_measure');
            $bin->description = $row->get('description');
            $bin->save();
            
          
            
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
        return 150;
    }

    public function chunkSize(): int
    {
        return 150;
    }
}
