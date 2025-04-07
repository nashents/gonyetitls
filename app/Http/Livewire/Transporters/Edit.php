<?php

namespace App\Http\Livewire\Transporters;

use Livewire\Component;
use App\Models\Transporter;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    use WithFileUploads;

    public $cargos;
    public $cargo_id;
    public $corridors;
    public $corridor_id;
    public $transporters;
    public $contact_name;
    public $contact_surname;
    public $contact_email;
    public $contact_phonenumber;
    public $department;
    public $name;
    public $phonenumber;
    public $email;
    public $country;
    public $city;
    public $suburb;
    public $street_address;
    public $worknumber;

    public $transporter_id;
    public $user_id;


    public $title;
    public $file;
    public $expires_at;

    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    public $contacts_inputs = [];
    public $o = 1;
    public $m = 1;

    public function contactsAdd($o)
    {
        $o = $o + 1;
        $this->o = $o;
        array_push($this->contacts_inputs ,$o);
    }

    public function contactsRemove($o)
    {
        unset($this->contacts_inputs[$o]);
    }

    public $corridors_inputs = [];
    public $l = 1;
    public $j = 1;

    public function corridorsAdd($l)
    {
        $l = $l + 1;
        $this->l = $l;
        array_push($this->corridors_inputs ,$l);
    }

    public function corridorsRemove($l)
    {
        unset($this->corridors_inputs[$l]);
    }

    public $cargos_inputs = [];
    public $s = 1;
    public $r = 1;

    public function cargosAdd($s)
    {
        $s = $s + 1;
        $this->s = $s;
        array_push($this->cargos_inputs ,$s);
    }

    public function cargosRemove($s)
    {
        unset($this->cargos_inputs[$s]);
    }

    private function resetInputFields(){
        $this->transporters = "";
        $this->contact_name = "";
        $this->contact_surname = "";
        $this->contact_email = "";
        $this->contact_phonenumber = "";
        $this->department = "";
        $this->name = "";
        $this->phonenumber = "";
        $this->worknumber = "";
        $this->email = "";
        $this->country = "";
        $this->city = "";
        $this->suburb = "";
        $this->street_address = "";
        $this->title = "";
        $this->file = "";
        $this->expires_at = "";
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:transporters,name,NULL,id,deleted_at,NULL|string|min:2',
        'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
        'phonenumber' => 'required',
        'worknumber' => 'required',
        'contact_name.0' => 'required|string|min:2',
        'contact_surname.0' => 'required|string|min:2',
        'contact_email.0' => 'required|email',
        'contact_phonenumber.0' => 'required|string|min:2',
        'country' => 'required|string|min:2',
        'city' => 'required|string|min:2',
        'suburb' => 'required|string|min:2',
        'street_address' => 'required|string|min:2',

    ];

    public function mount($transporter){
        $transporter = Transporter::find($transporter->id);
        $this->user_id = $transporter->user_id;
        $this->name = $transporter->name;
        $this->email = $transporter->email;
        $this->phonenumber = $transporter->phonenumber;
        $this->worknumber = $transporter->worknumber;
        $this->country = $transporter->country;
        $this->city = $transporter->city;
        $this->suburb = $transporter->suburb;
        $this->street_address = $transporter->street_address;
        $this->transporter_id = $transporter->id;
    }

    public function update()
    {
        if ($this->transporter_id) {
            try{
            $transporter = Transporter::find($this->transporter_id);
            $transporter->update([
                'user_id' => Auth::user()->id,
                'name' => $this->name,
                'phonenumber' => $this->phonenumber,
                'worknumber' => $this->worknumber,
                'email' => $this->email,
                'country' => $this->country,
                'city' => $this->city,
                'suburb' => $this->suburb,
                'street_address' => $this->street_address,
            ]);
          
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Transporter Updated Successfully!!"
            ]);
            return redirect()->route('transporters.index');
            }
                catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something went wrong while updating transporter!!"
                ]);
            }

        }
    }

    public function render()
    {
        return view('livewire.transporters.edit');
    }
}
