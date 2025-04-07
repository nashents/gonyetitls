<?php

namespace App\Http\Livewire;

use App\Models\Email;
use App\Models\Branch;
use App\Models\Driver;
use Livewire\Component;
use App\Mail\SendEmails;
use App\Models\Employee;
use App\Models\Attachment;
use App\Models\Department;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class Emails extends Component
{

    use WithFileUploads;
    
    public $email;
    public $emails;
    public $sending_emails;
    public $destination;
    public $body;
    public $subject;
    public $file;
    public $user_id;
    public $employee_id;
    public $employees;
    public $employee = NULL;
    public $drivers;
    public $driver = NULL;
    public $driver_id;
    public $department_id;
    public $department = NULL;
    public $departments;
    public $branch_id;
    public $branch = NULL;
    public $branches;

    public $selectedDriver;
    public $selectedEmployee;
    public $selectedDestination;
    public $selectedDepartment;
    public $selectedBranch;

    private function resetInputFields(){
        $this->selectedDestination = '';
        $this->selectedDepartment = '';
        $this->selectedDriver = '';
        $this->selectedEmployee = '';
        $this->selectedBranch = '';
        $this->subject = '';
        $this->body = '';
    }

    public function mount(){
        $this->emails = Email::where('user_id', Auth::user()->id)->orderBy('created_at','desc')->get();
        $this->branches = Branch::orderBy('name','asc')->get();
        $this->departments = Department::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->get();
        $this->drivers = Driver::latest()->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules =[
        'body'=>'required',
        'subject'=>'required',
        'selectedDestination'=>'required',
        'file'=>'nullable|file'
       
    ];

   
   
    public function updatedSelectedDestination($destination)
    {  
        $this->selectedDestination = $destination;
        if (!is_null($destination) ) {
            if ($destination == "employee") {
                $this->department = FALSE;
                $this->employee = TRUE;
                $this->driver = FALSE;
                $this->branch = FALSE;
            }elseif ($destination == "department") {
                $this->department = TRUE;
                $this->employee = FALSE;
                $this->driver = FALSE;
                $this->branch = FALSE;
            }elseif ($destination == "branch") {
                $this->department = FALSE;
                $this->employee = FALSE;
                $this->driver = FALSE;
                $this->branch = TRUE;
            }elseif ($destination == "driver") {
                $this->department = FALSE;
                $this->employee = FALSE;
                $this->driver = TRUE;
                $this->branch = FALSE;
            }elseif ($destination == "employees") {
                $this->employees = Employee::all();
            }elseif ($destination == "drivers") {
                $this->drivers = Employee::all();
            }
        }
    }
   

 public function send(){
    if ($this->selectedDestination == "employees") {
        $email = new Email;
        $email->user_id = Auth::user()->id;
        $email->destination = $this->selectedDestination;
        $email->subject = $this->subject;
        $email->body = $this->body;
        $email->save();

        if(isset($this->file)){
            $file = $this->file;
            // get file with ext
            $fileNameWithExt = $file->getClientOriginalName();
            //get filename
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            //get extention
            $extention = $file->getClientOriginalExtension();
            //file name to store
            $fileNameToStore= $filename.'_'.time().'.'.$extention;
            $file->storeAs('/attachments', $fileNameToStore, 'my_files');
        }

        $attachment = new Attachment;
        $attachment->email_id = $email->id;
        if (isset($fileNameToStore)) {
            $attachment->filename = $fileNameToStore;
        }
        $attachment->save();

        $data= array(
            'body'=> $this->body,
            'subject'=> $this->subject,
        );

        foreach ($this->employees as $employee) {
            if (!$employee->driver) {
                if ((isset($employee->email) && $employee->email != Null && $employee->email != "")) {
                    Mail::to($employee->email)->send(new SendEmails($data, $attachment));
                }
            }
          
        }
  
        $email->status = 1;
        $email->update();

        $this->dispatchBrowserEvent('hide-emailModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Email(s) Successfully Sent!!"
        ]);

    }elseif ($this->selectedDestination == "drivers") {
        $email = new Email;
        $email->user_id = Auth::user()->id;
        $email->destination = $this->selectedDestination;
        $email->subject = $this->subject;
        $email->body = $this->body;
        $email->save();

        if(isset($this->file)){
            $file = $this->file;
            // get file with ext
            $fileNameWithExt = $file->getClientOriginalName();
            //get filename
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            //get extention
            $extention = $file->getClientOriginalExtension();
            //file name to store
            $fileNameToStore= $filename.'_'.time().'.'.$extention;
            $file->storeAs('/attachments', $fileNameToStore, 'my_files');
        }

        $attachment = new Attachment;
        $attachment->email_id = $email->id;
        if (isset($fileNameToStore)) {
            $attachment->filename = $fileNameToStore;
        }
        $attachment->save();

        $data= array(
            'body'=> $this->body,
            'subject'=> $this->subject,
        );

        foreach ($this->drivers as $driver) {
            if (isset($driver->employee->email) && $driver->employee->email != Null && $driver->employee->email != "") {
                Mail::to($driver->employee->email)->send(new SendEmails($data, $attachment));
            }
        }
  
        $email->status = 1;
        $email->update();

        $this->dispatchBrowserEvent('hide-emailModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Email(s) Successfully Sent!!"
        ]);

    }

    elseif ($this->selectedDestination == "employee") {
        $this->employee = Employee::find($this->selectedEmployee);

        $email = new Email;
        $email->user_id = Auth::user()->id;
        $email->destination = $this->selectedDestination;
        $email->subject = $this->subject;
        $email->body = $this->body;
        $email->save();

        if(isset($this->file)){
            $file = $this->file;
            // get file with ext
            $fileNameWithExt = $file->getClientOriginalName();
            //get filename
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            //get extention
            $extention = $file->getClientOriginalExtension();
            //file name to store
            $fileNameToStore= $filename.'_'.time().'.'.$extention;
            $file->storeAs('/attachments', $fileNameToStore, 'my_files');
        }

        $attachment = new Attachment;
        $attachment->email_id = $email->id;
        if (isset($fileNameToStore)) {
            $attachment->filename = $fileNameToStore;
        }
        $attachment->save();

        $data= array(
            'body'=> $this->body,
            'subject'=> $this->subject,
        );

        if ($this->employee) {
            if (isset($this->employee->email) && $this->employee->email != Null && $this->employee->email != Null) {
                Mail::to($this->employee->email)->send(new SendEmails($data, $attachment));
            }
        }
          
        $email->status = 1;
        $email->update();

        $this->dispatchBrowserEvent('hide-emailModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Email Successfully Sent!!"
        ]);

    }elseif ($this->selectedDestination == "departments") {
        $department = Department::find($this->selectedDepartment);
        $this->employees = $department->employees;
        
        $email = new Email;
        $email->user_id = Auth::user()->id;
        $email->destination = $this->selectedDestination;
        $email->subject = $this->subject;
        $email->body = $this->body;
        $email->save();

        if(isset($this->file)){
            $file = $this->file;
            // get file with ext
            $fileNameWithExt = $file->getClientOriginalName();
            //get filename
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            //get extention
            $extention = $file->getClientOriginalExtension();
            //file name to store
            $fileNameToStore= $filename.'_'.time().'.'.$extention;
            $file->storeAs('/attachments', $fileNameToStore, 'my_files');
        }

        $attachment = new Attachment;
        $attachment->email_id = $email->id;
        if (isset($fileNameToStore)) {
            $attachment->filename = $fileNameToStore;
        }
        $attachment->save();

        $data= array(
            'body'=> $this->body,
            'subject'=> $this->subject,
        );

        foreach ($this->employees as $employee) {
            if (!$this->employee->driver) {
                if (isset($this->employee->email) && $this->employee->email != Null && $this->employee->email != "") {
                    Mail::to($this->employee->email)->send(new SendEmails($data, $attachment));
                }
            }
          
        }
  
        $email->status = 1;
        $email->update();

        $this->dispatchBrowserEvent('hide-emailModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Email(s) Successfully Sent!!"
        ]);
    }elseif ($this->selectedDestination == "branches") {
        $branch = Branch::find($this->selectedBranch);
        $this->employees = $branch->employees->pluck('email');
        
        $email = new Email;
        $email->user_id = Auth::user()->id;
        $email->destination = $this->selectedDestination;
        $email->subject = $this->subject;
        $email->body = $this->body;
        $email->save();

        $data= array(
            'body'=> $this->body,
            'subject'=> $this->subject,
        );

        foreach ($this->employees as $employee) {
            if (!$this->employee->driver) {
                if (isset($this->employee->email) && $this->employee->email != Null && $this->employee->email != "") {
                    Mail::to($this->employee->email)->send(new SendEmails($data));
                }
            }
          
        }
  
        $email->status = 1;
        $email->update();

        $this->dispatchBrowserEvent('hide-emailModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Email(s) Successfully Sent!!"
        ]);
    }
     
}



    // public function updatedSelectedEmployee($employee_id)
    // {
    //     if (!is_null($employee_id) ) {
    //         $this->employee_id = $employee_id;
    //         $employee = Employee::find($employee_id);
    //         $data= array(
    //             'body'=> $this->body,
    //             'email'=>$employee->email,
    //             'from' => Auth::user()->email,
    //             'attachment' => $this->file,
    //             'subject' => $this->subject
    //         );
    //         Mail::send('emails.email',$data, function($message) use($data){
    //             $message->to($data['email']);
    //             $message->subject($data['email']);
    //             $message->from($data['from']);    
    //             foreach ($this->files as $file) {
    //                 $message->attach($file);  
    //             }
                  
    //         });
    //         $mail = new Mail;
    //         $mail->employee_id = $this->employee_id ;
    //         $mail->destination = ucfirst($this->selectedDestination);
    //         $mail->subject = $this->subject;
    //         $mail->body = $this->body;
    //         $mail->save();
    //         if (isset($this->files)) {
    //             foreach ($this->files as $file) {
    //                     // get file with ext
    //                     $fileNameWithExt = $file->getClientOriginalName();
    //                     //get filename
    //                     $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
    //                     //get extention
    //                     $extention = $file->getClientOriginalExtension();
    //                     //file name to store
    //                     $fileNameToStore= $filename.'_'.time().'.'.$extention;
    //                     $file->storeAs('/documents', $fileNameToStore, 'my_files');
            
    //                 $attachment = new Attachment; 
    //                 $attachment->mail_id = $mail->id;
    //                 $attachment->filename = $fileNameToStore;
    //                 $attachment->save();
    //             }
    //         }
    //         Session::flash('success','Email successfully sent');
    //         return redirect()->route('emails.index');
            
    //     }
    // }
    // public function updatedSelectedDepartment($department_id)
    // {
    //     if (!is_null($department_id) ) {
    //         $this->department_id = $department_id;
    //         $department = Department::find($department_id);
    //         if ($department) {
    //             $employees = $department->employees;
    //         }
          
    //         foreach ($employees as $employee) {
    //             $data= array(
    //                 'body'=> $this->body,
    //                 'email'=>$employee->email,
    //                 'from' => Auth::user()->email,
    //                 'subject' => $this->subject
    //             );
    //             Mail::send('emails.email',$data, function($message) use($data){
    //                 $message->to($data['email']);
    //                 $message->subject($data['email']);
    //                 $message->from($data['from']);    
    //                 if (isset($this->files)) {
    //                     foreach ($this->files as $file) {
    //                         $message->attach($file);  
    //                     }
    //                 }
    //             });
    //         }
      
    //         $mail = new Mail;
    //         $mail->department_id = $this->department_id ;
    //         $mail->destination = ucfirst($this->selectedDestination);
    //         $mail->subject = $this->subject;
    //         $mail->body = $this->body;
    //         $mail->save();
    //         if (isset($this->files)) {
    //             foreach ($this->files as $file) {
    //                     // get file with ext
    //                     $fileNameWithExt = $file->getClientOriginalName();
    //                     //get filename
    //                     $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
    //                     //get extention
    //                     $extention = $file->getClientOriginalExtension();
    //                     //file name to store
    //                     $fileNameToStore= $filename.'_'.time().'.'.$extention;
    //                     $file->storeAs('/documents', $fileNameToStore, 'my_files');
            
    //                 $attachment = new Attachment; 
    //                 $attachment->mail_id = $mail->id;
    //                 $attachment->filename = $fileNameToStore;
    //                 $attachment->save();
    //             }
    //         }
    //         Session::flash('success','Email successfully sent');
    //         return redirect()->route('emails.index');
            
    //     }
    // }
    // public function updatedSelectedBranch($branch)
    // {
    //     if (!is_null($branch_id) ) {
    //         $this->branch_id = $branch_id;
    //         $branch = Branch::find($branch_id);
    //         $employees = $branch->employees;
    //         $data= array(
    //             'body'=> $this->body,
    //             'email'=>$employee->email,
    //             'from' => Auth::user()->email,
    //             'subject' => $this->subject

    //         );

    //         $this->sending_emails = Employee::where('driver_id', NULL)->get()->pluck('email');
    //         Mail::send('eemails.email',$data, function($message) use($data){
    //             $message->to($data['email']);
    //             $message->subject($data['email']);
    //             $message->from($data['from']);    
    //             foreach ($this->files as $file) {
    //                 $message->attach($file);  
    //             }
                  
    //         });
    //         $mail = new Email;
    //         $mail->branch_id = $this->branch_id ;
    //         $mail->destination = ucfirst($this->selectedDestination);
    //         $mail->subject = $this->subject;
    //         $mail->body = $this->body;
    //         $mail->save();
            
    //         if (isset($this->files)) {
    //             foreach ($this->files as $file) {
    //                     // get file with ext
    //                     $fileNameWithExt = $file->getClientOriginalName();
    //                     //get filename
    //                     $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
    //                     //get extention
    //                     $extention = $file->getClientOriginalExtension();
    //                     //file name to store
    //                     $fileNameToStore= $filename.'_'.time().'.'.$extention;
    //                     $file->storeAs('/documents', $fileNameToStore, 'my_files');
            
    //                 $attachment = new Attachment; 
    //                 $attachment->mail_id = $mail->id;
    //                 $attachment->filename = $fileNameToStore;
    //                 $attachment->save();
    //             }
    //         }
    //         Session::flash('success','Email successfully sent');
    //         return redirect()->route('emails.index');
            
    //     }
    // }
 

    public function render()
    {
        $this->emails = Email::where('user_id', Auth::user()->id)->orderBy('created_at','desc')->get();
        return view('livewire.emails',[
            'emails' => $this->emails
        ]);
    }
}
