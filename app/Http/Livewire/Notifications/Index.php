<?php

Namespace App\Http\Livewire\Notifications;

use Livewire\Component;
use App\Models\Employee;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    
    public $notifications;
    public $email;
    public $status;
    public $employee_id;
    public $employees;
    public $category;
 
    public $notification_id;
    public $user_id;
    public $notification;
  

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
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'category' => 'required',
    ];

    private function resetInputFields(){
        $this->email = '';
        $this->employee_id = '';
        $this->status = '';
        $this->category = '';

    }

   
    public function store(){
        // try{
        $notification = new Notification;
        $notification->user_id = Auth::user()->id;
        $notification->employee_id = $this->employee_id;
        $notification->category = $this->category;
        $notification->email = $this->email;
        $notification->status = $this->status;
        $notification->save();

        $this->dispatchBrowserEvent('hide-notificationModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Notification Created Successfully!!"
        ]);

        // return redirect()->route('notifications.index');

    //     }
    //     catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something goes wrong while creating notification!!"
    //     ]);
    // }
    }

    public function edit($id){
    $notification = Notification::find($id);
    $this->user_id = $notification->user_id;
    $this->employee_id = $notification->employee_id;
    $this->category = $notification->category;
    $this->email = $notification->email;
    $this->status = $notification->status;
    $this->notification_id = $notification->id;
    $this->dispatchBrowserEvent('show-notificationEditModal');

    }


    public function update()
    {
        if ($this->notification_id) {
            // try{
            $notification = Notification::find($this->notification_id);
            $notification->employee_id = $this->employee_id;
            $notification->category = $this->category;
            $notification->email = $this->email;
            $notification->status = $this->status;
            $notification->update();

            $this->dispatchBrowserEvent('hide-notificationEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Notification Updated Successfully!!"
            ]);


            // return redirect()->route('notifications.index');
        //     }
        //     catch(\Exception $e){
        //     $this->dispatchBrowserEvent('hide-notificationEditModal');
        //     $this->dispatchBrowserEvent('alert',[
        //         'type'=>'error',
        //         'message'=>"Something goes wrong while creating notification!!"
        //     ]);
        //   }
        }
    }


    public function render()
    {
        $this->notifications = Notification::latest()->get();
        return view('livewire.notifications.index',[
            'notifications'=>   $this->notifications
        ]);
    }
}
