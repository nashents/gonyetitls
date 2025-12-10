<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Category;
use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function login()
    {

        return view('authentication.login');
    }

    public function signup()
    {
        $roles = Role::all();
        return view('authentication.signup')->with('roles', $roles);
    }

    public function adminSignup()
    {
        $roles = Role::all();
        return view('authentication.admin-signup')->with('roles', $roles);
    }

    public function postLogin(Request $request)
    {
        // 1) Validate input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // 3) Attempt login
        $credentials = $request->only('username', 'password');

        if (! Auth::attempt($credentials)) {
            Session::flash('error', 'Failed to authenticate user.');
            return back()->withInput($request->only('username'));
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $displayName = trim($user->name . ' ' . ($user->surname ?? ''));

        // 4) Account active check
        if ($user->active !== 1) {
            Auth::logout();
            Session::flash('error', 'Failed to login. User account suspended.');
            return back();
        }

        // 5) Category routing
        switch ($user->category) {
            case 'company':
                Session::flash('success', "Welcome to your company dashboard {$displayName}");
                return redirect()->route('dashboard.index');

            case 'agent':
            case 'customer':
            case 'broker':
                Session::flash('success', "Welcome to your company dashboard {$displayName}");
                return redirect()->route('dashboard.third_parties');

            case 'transporter':
                $transporter = $user->transporter;

                if ($transporter && $transporter->authorization === "approved") {
                    Session::flash('success', "Welcome to your company dashboard {$displayName}");
                    return redirect()->route('dashboard.third_parties');
                }

                Auth::logout();
                Session::flash('error', 'Failed to login. Account pending authorization.');
                return back();

            case 'employee':
            case 'driver':
            case 'admin':
                // Guard against missing relations
                $employee   = $user->employee;
                $company    = $employee->company ?? null;
                $roles      = $user->roles ?? collect();
                $ranks      = $employee->ranks ?? collect();
                $departments = $employee->departments ?? collect();

                if (! $company || $company->status !== 1) {
                    Auth::logout();
                    Session::flash('error', 'Failed to login. Company account suspended.');
                    return back();
                }

                if ($roles->isEmpty() || $ranks->isEmpty() || $departments->isEmpty()) {
                    Auth::logout();
                    Session::flash('error', 'Failed to login. User Role | Rank | Department is not defined.');
                    return back();
                }

                $user->last_login_at = now();
                $user->save();

                Session::flash('success', "Welcome to your admin dashboard {$displayName}");
                return redirect()->route('dashboard.index');

            default:
                // Unknown category – don’t let them in
                Auth::logout();
                Session::flash('error', 'Failed to login. User category is not recognized.');
                return back();
        }
    }


  

    private function sendResetEmail($email, $token)
    {
    //Retrieve the user from the database
    $user = DB::table('users')->where('email', $email)->select('name', 'email')->first();
    //Generate, the password reset link. The token generated is embedded in the link
    $link = 'http://fundiso.co.zw/' . $token . '/reset-password';

    $data= array(
        'name'=> $user->name,
        'email'=>$user->email,
        'link'=>$link
    );
    Mail::send('emails.verify',$data, function($message) use($data){
        $message->to($data['email']);
        $message->subject('Reset Password Notification');
        $message->from("no-reply@fundiso.co.zw");
    });
}

    public function postEmail(Request $request){

        $this->validate($request,[
            'email'=>'required|email|exists:users'
        ]);

        $user = User::where('email', $request->email)->first();

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => Str::random(60),
            'created_at' => Carbon::now()
        ]);


        $tokenData = DB::table('password_resets')
        ->where('email', $request->email)->first();

        if ($this->sendResetEmail($request->email, $tokenData->token)) {
            Session::flash('success','A reset link has been sent to your email address.');
            return redirect()->back();
        } else {
            Session::flash('success','A reset link has been sent to your email address.');
            return redirect()->back();
        }

       }

    public function forgotPassword()
    {
        return view('authentication.forgot-password');
    }
    
    public function resetPassword($token, $id)
    {
        $user = User::find($id);
        return view('authentication.reset-password')->with([
                'token' => $token,
                'user' => $user,
            ]);
    }

    public function logout(Request $request){
        Auth::logout();
        Session::flash('success','Logout Successful');
        return redirect('/');
    }
}
