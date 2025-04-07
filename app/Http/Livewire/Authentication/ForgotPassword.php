<?php

namespace App\Http\Livewire\Authentication;

use Carbon\Carbon;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Mail\ForgotPasswordMail;
use App\Mail\PasswordResetEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPassword extends Component
{
    public $username;
    public $user;
    public $message = "";
    public $email;


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

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'username' => 'required|exists:users',
    ];

    private function sendResetEmail($email, $token, $user, $company)
    {
      
        //Retrieve the user from the database
        $user = DB::table('users')->where('email', $email)->select('name', 'email')->first();
        //Generate, the password reset link. The token generated is embedded in the link
        $link = $company->website . $token . "/reset-password"."/".$this->user->id;
        Mail::to($email)->send(new ForgotPasswordMail($user, $company, $link));


       
    }

    public function findAccount(){

        if (User::where('username', $this->username)->exists()) {
            $this->user = User::where('username',$this->username)->first();
            $this->message = "Account with username found.";
            
            DB::table('password_resets')->insert([
                'username' => $this->username,
                'token' => Str::random(60),
                'created_at' => Carbon::now()
            ]);
    
            $tokenData = DB::table('password_resets')
            ->where('username', $this->username)->first();

            $this->email = $this->user->employee->email;
            $this->phonenumber = $this->user->employee->phonenumber;

            if (isset($this->user->company)) {
                $company = $this->user->company;
            }elseif (isset($this->user->employee->company)) {
                $company = $this->user->employee->company;
            }
            if (isset($this->email)) {
                $this->sendResetEmail($this->email, $tokenData->token, $this->user, $company);
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Pin reset email successfully sent to your email"
                ]);
            }
            // else {
                
            // }
            
      
   
        }else {
            $this->message = "Account with username not found.";
        }
    }

   


    public function render()
    {
        return view('livewire.authentication.forgot-password');
    }
}
