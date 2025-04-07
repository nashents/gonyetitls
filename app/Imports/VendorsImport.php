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
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class VendorsImport implements  ToCollection,
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading
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
 

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {


       foreach($rows as $row){
        if($row->filter()->isNotEmpty()){

        $pin =  $this->generatePIN();

        $user = User::where('name',$row['name'])->get()->first();
        $vendor = Vendor::where('name',$row['name'])->get()->first();

        if (isset($user) && isset($vendor)) {
            $user->name = $row['name'];
            $user->category  = 'vendor';
            $user->is_admin = '0';
            $user->active = '1';
            $user->email = $row['email'];
            $user->update();
            $user->roles()->attach([3]);

            $vendor->name    = $row['name'];
            $vendor->email    = $row['email'];
            $vendor->pin    = $pin;
            $vendor->phonenumber    = $row['phonenumber'];
            $vendor->worknumber    = $row['worknumber'];
            $vendor->country    = $row['country'];
            $vendor->city    = $row['city'];
            $vendor->suburb    = $row['suburb'];
            $vendor->street_address    = $row['streetaddress'];
            $vendor->update();
           
        } else {
              
      

        $user = User::create([
            'name'     => $row['name'],
            'category'     => 'vendor',
            'is_admin'     => '0',
            'active'     => '1',
            'email'     => $row['email'],
            'password'    => Hash::make($pin),

        ]);
        $user->roles()->attach([3]);
      
        $vendor = new Vendor;
        
        $vendor->company_id     = Auth::user()->employee->company_id;
        $vendor->creator_id     = Auth::user()->id;
        $vendor->user_id     = $user->id;
        $vendor->vendor_number   = $this->vendorNumber();
        $vendor->name    = $row['name'];
        $vendor->email    = $row['email'];
        $vendor->pin    = $pin;
        $vendor->phonenumber    = $row['phonenumber'];
        $vendor->worknumber    = $row['worknumber'];
        $vendor->country    = $row['country'];
        $vendor->city    = $row['city'];
        $vendor->suburb    = $row['suburb'];
        $vendor->street_address    = $row['streetaddress'];
        $vendor->save();
      
        }
        
     
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

    public function chunkSize(): int
    {
        return 1000;
    }
}
