<?php

namespace App\Http\Livewire\Companies;

use App\Models\Rank;
use App\Models\Role;
use App\Models\Company;
use Livewire\Component;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;

class Manage extends Component
{

    public $name;
    public $email;
    public $selectedType;
    public $selectedPlan;
    public $fee;
    public $role_id;
    public $roles;
    public $license_currency_id;
    public $rank_id;
    public $status;
    public $ranks;
    public $website;
    public $noreply;
    public $phonenumber;
    public $country;
    public $city;
    public $suburb;
    public $street_address;
    public $companies;
    public $company_id;
    public $user_id;
    public $admin_id;

    public function mount(){
        if (Auth::user()->is_admin()) {
            $this->companies = Company::latest()->get();
        }else {
            $this->companies = Company::where('type','!=','admin')->get();
        }
        $this->roles = Role::all();
        $this->currencies = Currency::all();
        $this->noreply = 'noreply@gonyetitls.co.zw';
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:companies,name,NULL,id,deleted_at,NULL|string|min:2',
        'phonenumber' => 'required|unique:companies,phonenumber,NULL,id,deleted_at,NULL',
        'email' => 'required|unique:users,email,NULL,id,deleted_at,NULL',
        'country' => 'required',
        'city' => 'required',
        'selectedType' => 'required',
        'selectedPlan' => 'required',
        'fee' => 'required',
        'license_currency_id' => 'required',
        'status' => 'required',
        'suburb' => 'required',
        'rank_id' => 'required',
        'role_id' => 'required',
        'street_address' => 'required',
    ];

    private function resetInputFields(){
        $this->email = '';
        $this->phonenumber = '';
        $this->country = '';
        $this->selectedType = '';
        $this->selectedPlan = '';
        $this->license_currency_id = '';
        $this->fee = '';
        $this->status = '';
        $this->city = '';
        $this->suburb = '';
        $this->street_address = '';
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
    public function edit($id){
        $company = Company::find($id);
        $this->user_id = $company->user_id;
        $this->admin_id = $company->admin_id;
        $this->name = $company->name;
        $this->phonenumber = $company->phonenumber;
        $this->selectedType = $company->type;
        $this->status = $company->status;
        $this->selectedPlan = $company->plan;
        $this->fee = $company->fee;
        $this->noreply = $company->noreply;
        $this->website = $company->website;
        $this->license_currency_id = $company->license_currency_id;
        $this->email = $company->email;
        $this->country = $company->country;
        $this->city = $company->city;
        $this->suburb = $company->suburb;
        $this->street_address = $company->street_address;
        $this->company_id = $company->id;
        $this->dispatchBrowserEvent('show-companyEditModal');

        }


        public function update()
        {
            if ($this->company_id) {
                try{
                $company = Company::find($this->company_id);

                $user = $company->user;
                $user->name = $this->name;
                $user->email = $this->email;
                $user->update();

                $company->user_id = $this->user_id;
                $company->admin_id = Auth::user()->id;
                $company->name = $this->name;
                $company->type = $this->selectedType;
                $company->status = $this->status;
                $company->plan = $this->selectedPlan;
                $company->fee = $this->fee;
                $company->phonenumber = $this->phonenumber;
                $company->email = $this->email;
                $company->noreply = $this->noreply;
                $company->license_currency_id = $this->license_currency_id;
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


                // return redirect()->route('companies.index');
                }
                catch(\Exception $e){
                $this->dispatchBrowserEvent('hide-companyEditModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something goes wrong while creating company!!"
                ]);
              }
            }
        }

    public function render()
    {
        if (Auth::user()->is_admin()) {
            $this->companies = Company::latest()->get();
        }else {
            $this->companies = Company::where('type','!=','admin')->get();
        }
        return view('livewire.companies.manage',[
            'companies' => $this->companies
        ]);
    }
}
