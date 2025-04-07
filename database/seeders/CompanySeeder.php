<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Transporter;
use Illuminate\Database\Seeder;
use App\Mail\AccountCreationMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

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

    public function run()
    {
        $password = Hash::make('admin@gonyetitls');
        $currency = Currency::where('name','USD')->first();
        $user = User::create([
            'name' => 'Gonyeti TLS',
            'category' => 'company',
            'email' => 'info@gonyetitls.com',
            'password' => $password,
        ]);
     
        $company = Company::create([
            'user_id' => $user->id,
            'type' => 'Admin',
            'name' => 'Gonyeti TLS',
            'email' => 'info@gonyetitls.com',
            'noreply' => 'noreply@gonyetitls.com',
            'phonenumber' => '0782421799',
            'country' => 'Zimbabwe',
            'currency_id' => $currency->id,
            'city' => 'Harare',
            'suburb' => 'Waterfalls',
            'street_address' => '271 Northway Ave',
        ]);

        $pin = $this->generatePIN();

        $transporter_user = new User;
        $transporter_user->name = "Gonyeti TLS";
        $transporter_user->category = 'transporter';
        $transporter_user->email = "info@gonyetitls.com";
        $transporter_user->password = Hash::make($pin);
        $transporter_user->save();

        // Mail::to('info@gonyetitls.com')->send(new AccountCreationMail($transporter_user, $company,$pin));

        $transporter = new Transporter;
        $transporter->creator_id = $user->id;
        $transporter->company_id = $company->id;
        $transporter->user_id = $transporter_user->id;
        $transporter->name = "Gonyeti TLS";
        $transporter->transporter_number = "GTT00001";
        $transporter->email = "info@gonyetitls.com";
        $transporter->pin = $pin;
        $transporter->phonenumber ='0782421799';
        $transporter->country = 'Zimbabwe';
        $transporter->city = 'Harare';
        $transporter->suburb = 'Waterfalls';
        $transporter->street_address = '271 Northway Ave';
        $transporter->authorization = "approved";
        $transporter->status = 1;
        $transporter->save();

    }
}
