<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Count;
use App\Models\Vendor;
use App\Models\VendorType;
use App\Imports\VendorsImport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

class VendorsImport implements  ToCollection, SkipsEmptyRows, WithLimit, 
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts

{
    use Importable, SkipsErrors;

    public function generatePIN($digits = 4){
        $i = 0; //counter
        $pin = ""; //our default pin is blank.
        while($i < $digits){
            //generate a random number between 0 and 9.
            $pin .= mt_rand(0, 9);
            $i++;
        }
        return $pin;
    }

    public function vendorNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

            $vendor = Vendor::orderBy('id', 'desc')->first();

        if (!$vendor) {
            $vendor_number =  $initials .'C'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $vendor->id + 1;
            $vendor_number =  $initials .'C'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $vendor_number;


    }

     public function limit(): int
    {
        return 2500; // Import only the first 100 rows
    }
 

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {


       foreach($rows as $row){
        if($row->filter()->isNotEmpty()){

        $pin = $this->generatePIN();

        $name = $row->get('name');
        $email = $row->get('email');
        $phonenumber = $row->get('phonenumber');
        $worknumber = $row->get('worknumber');
        $country = $row->get('country');
        $city = $row->get('city');
        $suburb = $row->get('suburb');
        $street = $row->get('streetaddress');

        $user = User::firstOrNew(['name' => $name]);
        $user->category = 'vendor';
        $user->is_admin = '0';
        $user->active = '1';
        $user->email = $email;

        if (!$user->exists) {
            $user->password = Hash::make($pin);
        }

        $user->save();

        if (!$user->roles()->where('role_id', 3)->exists()) {
            $user->roles()->attach([3]);
        }

        $vendor = Vendor::firstOrNew(['name' => $name]);

        if (!$vendor->exists) {
            $vendor->company_id = Auth::user()->employee->company_id;
            $vendor->creator_id = Auth::user()->id;
            $vendor->user_id = $user->id;
            $vendor->vendor_number = $this->vendorNumber();
        }

        $vendor->name = $name;
        $vendor->email = $email;
        $vendor->pin = $pin;
        $vendor->phonenumber = $phonenumber;
        $vendor->worknumber = $worknumber;
        $vendor->country = $country;
        $vendor->city = $city;
        $vendor->suburb = $suburb;
        $vendor->street_address = $street;

        $vendor->save();
        
     
    }
       }
    }

    public function rules(): array{
        return[
            // '*.name' => ['nullable','unique:users,name,NULL,id,deleted_at,NULL'],
            // '*.email' => ['nullable','unique:users,email,NULL,id,deleted_at,NULL'],
            // '*.phonenumber' => ['nullable','unique:vendors,phonenumber,NULL,id,deleted_at,NULL'],
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
