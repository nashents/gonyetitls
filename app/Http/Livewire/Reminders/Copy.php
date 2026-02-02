<?php

namespace App\Http\Livewire\Reminders;

use Livewire\Component;
use App\Models\Employee;
use App\Models\ReminderCopy;

class Copy extends Component
{

    protected $copies;
    public $user_id;
    public $selectedEmployee;
    public $employees;
    public $name, $surname, $email, $phonenumber, $copy_id, $status = 1;

    public function mount()
    {
        $this->user_id = auth()->id();
        $this->employees = Employee::whereNotNull('email')->orderBy('name', 'asc')->orderBy('surname', 'asc')->get();
    }

    public function resetInputFields(){
        $this->name = '';
        $this->surname = '';
        $this->email = '';
        $this->phonenumber = '';
        $this->status = '';
    }

    public function updatedSelectedEmployee($id)
    {
        if (!is_null($id)) {
            $employee = Employee::find($id);
            $this->name = $employee->name;
            $this->surname = $employee->surname;
            $this->email = $employee->email;
            $this->phonenumber = $employee->phonenumber;
        } 
    }

    public function store()
    {
        $this->validate([
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|email',
        ]);

        ReminderCopy::create([
            'user_id' => $this->user_id,
            'employee_id' => $this->selectedEmployee,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'phonenumber' => $this->phonenumber,
            'status' => $this->status,
        ]);

        session()->flash('message', 'Reminder Copy Created Successfully.');

     
    }

    public function delete($id)
    {
        $copy = ReminderCopy::find($id);
        $copy->delete();
        session()->flash('message', 'Reminder Copy Deleted Successfully.');
    }
    public function destroy($id)
    {
        $copy = ReminderCopy::find($id);
        $copy->delete();
        session()->flash('message', 'Reminder Copy Deleted Successfully.');
    }

    public function edit($id)
    {
        $copy = ReminderCopy::find($id);
        $this->name = $copy->name;
        $this->surname = $copy->surname;
        $this->email = $copy->email;
        $this->phonenumber = $copy->phonenumber;
        $this->copy_id = $id;
         $this->dispatchBrowserEvent('show-reminderCopyEditModal');
    }

    public function update()
    {
        $this->validate([
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|email',
            'phonenumber' => 'required',
        ]);

        $copy = ReminderCopy::find($this->copy_id);
        $copy->name = $this->name;
        $copy->surname = $this->surname;
        $copy->email = $this->email;
        $copy->phonenumber = $this->phonenumber;
        $copy->save();

        $this->dispatchBrowserEvent('hide-reminderCopyEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Copy Listing Updated Successfully!!"
        ]);
    }
    public function render()
    {
        return view('livewire.reminders.copy',[
            'copies' => ReminderCopy::where('user_id', $this->user_id)->get()
        ]);
    }
}
