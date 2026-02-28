<?php

namespace App\Http\Livewire\Reminders;

use App\Models\Employee;
use App\Models\ReminderCopy;
use Livewire\Component;
use Livewire\WithPagination;

class Copy extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
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
        $this->selectedEmployee = '';
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

        
        $this->dispatchBrowserEvent('hide-reminderCopyModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Email Copy Created Successfully!!"
        ]);

     
    }

    public function delete($id)
    {
        $this->copy_id = $id;
        $this->dispatchBrowserEvent('show-reminderCopyDeleteModal');
        
    }
    public function destroy()
    {
        $copy = ReminderCopy::find($this->copy_id);
        $copy->delete();
        $this->resetInputFields();
        $this->dispatchBrowserEvent('hide-reminderCopyDeleteModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Email Copy Deleted Successfully!!"
        ]);
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
            'message' => "Email Copy Updated Successfully!!"
        ]);
    }
    public function render()
    {
        $baseQuery = ReminderCopy::query()
        ->with('employee') // if you need employee data in the view
        ->where('user_id', $this->user_id)
        ->when($this->search, function ($query) {
            $search = '%'.$this->search.'%';

            $query->where(function ($q) use ($search) {
                // Employee name & surname
                $q->whereHas('employee', function ($emp) use ($search) {
                    $emp->where('name', 'like', $search)
                        ->orWhere('surname', 'like', $search);
                })
                // Email
                ->orWhere('email', 'like', $search)
                // Phone number
                ->orWhere('phone_number', 'like', $search);
            });
        })
        ->orderBy('email','asc')->paginate(10);
        return view('livewire.reminders.copy', [
        'copies' => $baseQuery,
]);
    }
}
