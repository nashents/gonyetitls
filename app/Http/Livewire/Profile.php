<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Mail\PasswordResetEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class Profile extends Component
{

    public $employee;
    public $user;
    public $pin;

    public function mount($user, $employee){
        $this->user = $user;
        $this->employee = $employee;
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

    public function resetPassword(){

        $pin = $this->generatePIN();
        $this->user->password = Hash::make($pin);
        $this->user->update();

        $employee = $this->user->employee;
        $employee->pin = $pin;
        $employee->update();

        if (isset($this->user->company)) {
            $company = $this->user->company;
        }elseif (isset($this->user->employee->company)) {
            $company = $this->user->employee->company;
        }
        
        $this->email = $this->user->employee->email;
       
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Pin reset was successful!!"
        ]);
        return redirect(request()->header('Referer'));

      
        
       
    }

    public function render()
    {
        $this->pin = $this->employee->pin;
        return view('livewire.profile',[
            'employee' =>$this->employee,
            'user' =>$this->user,
            'pin' =>$this->pin,
        ]);
    }
}
