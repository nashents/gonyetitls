<?php

namespace App\Http\Livewire\Authentication;

use Livewire\Component;
use App\Mail\PasswordResetEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ResetPassword extends Component
{
    public $token;
    public $user;
    public $company;
    public $email;
    public $message;


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

    public function mount($token, $user){

        $this->token = $token;
        $this->user = $user;

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

        if (isset($this->email)) {
            Mail::to($this->email)->send(new PasswordResetEmail($this->user, $company,$pin));
        }
        $this->message = "Congratulations your password reset and sent to your email successfully!!";
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Pin reset was successful, please check your email!!"
        ]);
        
       
    }

    public function render()
    {
        return view('livewire.authentication.reset-password');
    }
}
