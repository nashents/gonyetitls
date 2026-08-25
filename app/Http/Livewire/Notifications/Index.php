<?php

Namespace App\Http\Livewire\Notifications;

use Livewire\Component;
use App\Models\Employee;
use App\Models\EditAuthorizer;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    public $notifications;
    public $email;
    public $status;
    public $employee_id;
    public $employees;
    public $category;
    public $when;

    public $notification_id;
    public $user_id;
    public $notification;

    public $isSuperAdmin = false;
    public $trip_edit_authorizers;
    public $trip_edit_authorizer_users;
    public $trip_edit_authorizer_id;
    public $trip_edit_authorizer_user_id;
    public $trip_edit_authorizer_status;
    public $trip_edit_authorizer_module;
    public $edit_authorization_modules;


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

    public function mount(){
        $this->status = 1;
        $this->notifications = Notification::orderBy('created_at','desc');
        $this->employees = Employee::where('email','!=','')->where('email','!=',Null)->orderBy('name','asc')->get();

        $this->isSuperAdmin = Auth::user()->isSuperAdmin();
        if ($this->isSuperAdmin) {
            $this->trip_edit_authorizer_status = 1;
            $this->trip_edit_authorizer_module = 'trips';
            $this->trip_edit_authorizer_users = User::where('category', 'employee')->where('active', 1)->orderBy('name', 'asc')->get();
            $this->edit_authorization_modules = collect(config('edit_authorization'))->map(fn ($cfg, $key) => ['key' => $key, 'label' => $cfg['label']])->values();
        }
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'category' => 'required',
        'when' => 'required',
        'email' => 'nullable|email',
    ];

    private function resetInputFields(){
        $this->email = Null;
        $this->employee_id = Null;
        $this->status = '';
        $this->category = '';
        $this->when = '';

    }

   
    public function store(){
        

        $this->validate();

         DB::transaction(function () {

        $notification = new Notification;
        $notification->user_id = Auth::user()->id;
        $notification->employee_id = $this->employee_id;
        $notification->category = $this->category;
        $notification->when = $this->when;
        $notification->email = $this->email;
        $notification->status = $this->status;
        $notification->save();

        $this->dispatchBrowserEvent('hide-notificationModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Notification Created Successfully!!"
        ]);

         });
    }

    public function edit($id){
        $notification = Notification::find($id);
        $this->user_id = $notification->user_id;
        $this->employee_id = $notification->employee_id;
        $this->category = $notification->category;
        $this->when = $notification->when;
        $this->email = $notification->email;
        $this->status = $notification->status;
        $this->notification_id = $notification->id;
        $this->dispatchBrowserEvent('show-notificationEditModal');

    }


    public function update()
    {

        $this->validate();

        DB::transaction(function () {

        if ($this->notification_id) {
           
            $notification = Notification::find($this->notification_id);
            $notification->employee_id = $this->employee_id;
            $notification->category = $this->category;
            $notification->when = $this->when;
            $notification->email = $this->email;
            $notification->status = $this->status;
            $notification->update();

            $this->dispatchBrowserEvent('hide-notificationEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Notification Updated Successfully!!"
            ]);


         
        }

         });
    }


    private function resetTripEditAuthorizerFields(){
        $this->trip_edit_authorizer_id = null;
        $this->trip_edit_authorizer_user_id = null;
        $this->trip_edit_authorizer_status = 1;
        $this->trip_edit_authorizer_module = 'trips';
    }

    public function storeTripEditAuthorizer(){
        abort_unless($this->isSuperAdmin, 403);

        $this->validate([
            'trip_edit_authorizer_user_id' => 'required',
            'trip_edit_authorizer_module' => 'required',
        ]);

        DB::transaction(function () {
            $authorizer = new EditAuthorizer;
            $authorizer->user_id = $this->trip_edit_authorizer_user_id;
            $authorizer->module = $this->trip_edit_authorizer_module;
            $authorizer->created_by = Auth::user()->id;
            $authorizer->status = $this->trip_edit_authorizer_status;
            $authorizer->save();

            $this->dispatchBrowserEvent('hide-tripEditAuthorizerModal');
            $this->resetTripEditAuthorizerFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Edit Authorizer Added Successfully!!"
            ]);
        });
    }

    public function editTripEditAuthorizer($id){
        abort_unless($this->isSuperAdmin, 403);

        $authorizer = EditAuthorizer::find($id);
        $this->trip_edit_authorizer_id = $authorizer->id;
        $this->trip_edit_authorizer_user_id = $authorizer->user_id;
        $this->trip_edit_authorizer_status = $authorizer->status;
        $this->trip_edit_authorizer_module = $authorizer->module;
        $this->dispatchBrowserEvent('show-tripEditAuthorizerEditModal');
    }

    public function updateTripEditAuthorizer(){
        abort_unless($this->isSuperAdmin, 403);

        $this->validate([
            'trip_edit_authorizer_user_id' => 'required',
            'trip_edit_authorizer_module' => 'required',
        ]);

        DB::transaction(function () {
            if ($this->trip_edit_authorizer_id) {
                $authorizer = EditAuthorizer::find($this->trip_edit_authorizer_id);
                $authorizer->user_id = $this->trip_edit_authorizer_user_id;
                $authorizer->module = $this->trip_edit_authorizer_module;
                $authorizer->status = $this->trip_edit_authorizer_status;
                $authorizer->update();

                $this->dispatchBrowserEvent('hide-tripEditAuthorizerEditModal');
                $this->resetTripEditAuthorizerFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Edit Authorizer Updated Successfully!!"
                ]);
            }
        });
    }

    public function deleteTripEditAuthorizer($id){
        abort_unless($this->isSuperAdmin, 403);

        $authorizer = EditAuthorizer::find($id);
        if ($authorizer) {
            $authorizer->delete();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Edit Authorizer Removed Successfully!!"
            ]);
        }
    }

    public function render()
    {
        $this->notifications = Notification::latest()->get();
        if ($this->isSuperAdmin) {
            $this->trip_edit_authorizers = EditAuthorizer::with('user', 'creator')->latest()->get();
        }
        return view('livewire.notifications.index',[
            'notifications'=>   $this->notifications
        ]);
    }
}
