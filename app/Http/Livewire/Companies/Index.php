<?php

namespace App\Http\Livewire\Companies;

use App\Models\Rank;
use App\Models\Role;
use App\Models\User;
use App\Models\Company;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Transporter;
use App\Mail\AccountCreationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class Index extends Component
{
    public $name;
    public $email;
    public $selectedType;
    public $selectedPlan;
    public $license_currency_id;
    public $fee;
    public $role_id = [];
    public $rank_id;
    public $ranks;
    public $website;
    public $roles;
    public $phonenumber;
    public $country;
    public $city;
    public $status;
    public $noreply;
    public $suburb;
    public $street_address;
    public $companies;
    public $company_id;
    public $user_id;
    public $admin_id;
    public $expiry_date;
    public $currencies;
    public $currency_id;
    public $company;
    public $user;

    public function mount(){
        
        $this->user = User::find(Auth::user()->id);
        $this->roles = Role::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->noreply = 'noreply@gonyetitls.com';

    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        
        'name' => 'required|unique:companies,name,NULL,id,deleted_at,NULL|string|min:2',
        'phonenumber' => 'required|unique:companies,phonenumber,NULL,id,deleted_at,NULL',
        'email' => 'required|unique:users,email,NULL,id,deleted_at,NULL',
        'selectedType' => 'required',
        'selectedPlan' => 'required',
        'fee' => 'required',
        'status' => 'required',
        'country' => 'required',
        'city' => 'required',
        'suburb' => 'required',
        'role_id' => 'required',
        'street_address' => 'required',
        'expiry_date' => 'required',
    ];

    private function resetInputFields(){
        $this->email = '';
        $this->phonenumber = '';
        $this->country = '';
        $this->city = '';
        $this->selectedType = '';
        $this->selectedPlan = '';
        $this->license_currency_id = '';
        $this->fee = '';
        $this->status = '';
        $this->expiry_date = '';
        $this->website = '';
        $this->suburb = '';
        $this->street_address = '';
        $this->role_id = '';
        $this->name = '';
    }
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

    public function transporterNumber(){

        if (isset($this->company)) {
            $str =$this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset($this->company)) {
            $str = $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

            $transporter = Transporter::orderBy('id','desc')->first();

        if (!$transporter) {
            $transporter_number =  $initials .'T'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $transporter->id + 1;
            $transporter_number =  $initials .'T'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $transporter_number;


    }

    public function updatedSelectedPlan($plan){
        if (!is_null($plan)) {
            if ($plan == "10") {
                $this->fee = 50;
            }
            elseif ($plan == "25") {
                $this->fee = 100;
            }
            elseif ($plan == "50") {
                $this->fee = 150;
            }
            elseif ($plan == "75") {
                $this->fee = 200;
            }
            elseif ($plan == "100") {
                $this->fee = 250;
            }elseif ($plan == "125") {
                $this->fee = 300;
            }elseif ($plan == "150") {
                $this->fee = 350;
            }
            elseif ($plan == "175") {
                $this->fee = 400;
            }
            elseif ($plan == "200") {
                $this->fee = 450;
            }
        }
    }
    public function updatedSelectedType($type){
        if (!is_null($type)) {
            if ($type == "Broker") {
                $this->fee = 100;
            }
        }
    }

    public function store(){
    
        DB::transaction(function () {


        $pin = $this->generatePIN();

        $user = new User;
        $user->name = $this->name;
        $user->category = 'company';
        $user->email = $this->email;
        $user->username = $this->email;
        $user->password = Hash::make($pin);
        $user->save();
        $user->roles()->attach($this->role_id);


        $company = new Company;
        $company->admin_id = Auth::user()->id;
        $company->user_id = $user->id;
        $company->type = $this->selectedType;
        $company->name = $this->name;
        $company->email = $this->email;
        $company->plan = $this->selectedPlan;
        $company->currency_id = $this->license_currency_id ? $this->license_currency_id : Null;
        $company->license_currency_id = $this->license_currency_id ? $this->license_currency_id : Null;
        $company->fee = $this->fee;
        $company->expiry_date = $this->expiry_date;
        $company->noreply =  $this->noreply;
        $company->username = $this->email;
        $company->website = $this->website;
        $company->pin = $pin;
        $company->phonenumber = $this->phonenumber;
        $company->country = $this->country;
        $company->city = $this->city;
        $company->suburb = $this->suburb;
        $company->street_address = $this->street_address;
        $company->save();
        $this->company = $company;
        $this->company_id = $company->id;

        $pin = $this->generatePIN();

        $transporter_user = new User;
        $transporter_user->name = $this->name;
        $transporter_user->category = 'transporter';
        $transporter_user->email = $this->email;
        $transporter_user->username = $this->email;
        $transporter_user->password = Hash::make($pin);
        $transporter_user->save();

       

        $transporter = new Transporter;
        $transporter->creator_id = Auth::user()->id;
        $transporter->company_id = $company->id;
        $transporter->user_id = $transporter_user->id;
        $transporter->name = $this->name;
        $transporter->transporter_number = $this->transporterNumber();
        $transporter->email = $this->email;
        $transporter->pin = $pin;
        $transporter->phonenumber = $this->phonenumber;
        $transporter->country = $this->country;
        $transporter->city = $this->city;
        $transporter->suburb = $this->suburb;
        $transporter->street_address = $this->street_address;
        $transporter->status = 1;
        $transporter->save();

       
        if ($this->email) {
            Mail::to($this->email)->send(new AccountCreationMail($user, $company,$pin));
            Mail::to($this->email)->send(new AccountCreationMail($transporter_user, $company,$pin));
        }

        $this->dispatchBrowserEvent('hide-companyModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Company Created Successfully!!"
        ]);

     });

    }

    public function edit($id){
        $company = Company::find($id);
        $this->user_id = $company->user_id;
        $this->admin_id = $company->admin_id;
        $this->name = $company->name;
        $this->selectedType = $company->type;
        $this->status = $company->status;
        $roles = $company->user->roles;
        
        if (isset($roles)) {
            foreach ($roles as $role) {
                $this->role_id[] = $role->id;
            }
          
        }
       
        $this->selectedPlan = $company->plan;
        $this->fee = $company->fee;
        $this->license_currency_id = $company->license_currency_id;
        $this->phonenumber = $company->phonenumber;
        $this->email = $company->email;
        $this->noreply = $company->noreply;
        $this->website = $company->website;
        $this->country = $company->country;
        $this->expiry_date = $company->expiry_date;
        $this->city = $company->city;
        $this->suburb = $company->suburb;
        $this->street_address = $company->street_address;
        $this->company_id = $company->id;
        $this->dispatchBrowserEvent('show-companyEditModal');

        }


        public function update()
        {
            DB::transaction(function () {
                if ($this->company_id) {
                    $company = Company::find($this->company_id);
                    
                    $user = $company->user;
                    $user->name = $this->name;
                    $user->email = $this->email;
                    $user->username = $this->email;
                    $user->update();
                    $user->roles()->detach();
                    $user->roles()->sync($this->role_id);

                    $company->user_id = $this->user_id;
                    $company->admin_id = Auth::user()->id;
                    $company->name = $this->name;
                    $company->type = $this->selectedType;
                    $company->phonenumber = $this->phonenumber;
                    $company->email = $this->email;
                    $company->currency_id = $this->license_currency_id ? $this->license_currency_id : Null;
                    $company->license_currency_id = $this->license_currency_id ? $this->license_currency_id : Null;
                    $company->status = $this->status;
                    $company->plan = $this->selectedPlan;
                    $company->expiry_date = $this->expiry_date;
                    $company->fee = $this->fee;
                    $company->noreply = $this->noreply;
                    $company->website = $this->website;
                    $company->username = $this->email;
                    $company->country = $this->country;
                    $company->city = $this->city;
                    $company->suburb = $this->suburb;
                    $company->street_address = $this->street_address;
                    $company->update();

                    $this->dispatchBrowserEvent('hide-companyEditModal');
                    $this->resetInputFields();
                    $this->dispatchBrowserEvent('alert',[
                        'type'=>'success',
                        'message'=>"Company Updated Successfully!!"
                    ]);

                    return redirect(request()->header('Referer'));
                }

            });
        }

    public function render()
    {
        if ($this->user->is_admin()) {
            $this->companies = Company::orderBy('name','asc')->get();
        }else {
            $this->companies = Company::where('type','!=','admin')->orderBy('name','asc')->get();
        }
        return view('livewire.companies.index',[
            'companies' => $this->companies
        ]);
    }
}
