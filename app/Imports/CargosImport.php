<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Count;
use App\Models\Cargo;
use App\Imports\CargosImport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CargosImport implements  ToCollection,
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


            $cargo = Cargo::where('name',$row['name'])->first();

            if (isset($cargo)) {
               
                $cargo = Cargo::where('name',$row['name'])->get()->first();
                $cargo->type   = ucfirst($row['type']);
                $cargo->group    = ucfirst($row['group']);
                $cargo->name     = ucfirst($row['name']);
                $cargo->measurement     = ucfirst($row['measurement']);
                $cargo->risk     = ucfirst($row['risk']);
                $cargo->update();
                
              
            } else {
                $cargo = new Cargo;
                $cargo->user_id     = Auth::user()->id;
                $cargo->type   = ucfirst($row['type']);
                $cargo->group    = ucfirst($row['group']);
                $cargo->name     = ucfirst($row['name']);
                $cargo->measurement     = ucfirst($row['measurement']);
                $cargo->risk     = ucfirst($row['risk']);
                $cargo->save();
            }
            

           
            
    }
       }
    }

    public function rules(): array{
        return[
            '*.name' => ['required'],
            '*.type' => ['required'],
        ];
    }




    public function chunkSize(): int
    {
        return 1000;
    }
}
