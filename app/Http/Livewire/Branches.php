<?php

namespace App\Http\Livewire;

use App\Models\Branch;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Branches extends Component
{
    public $branches;
    public $name;
    public $email;
    public $phonenumber;
    public $address;
    public $updateMode = false;
    public $deleteMode = false;

    public $branch_id;
    public $user_id;

    public function mount(){
        $this->branches = Branch::latest()->get();
    }
    private function resetInputFields(){
        $this->name = '';
        $this->email = '';
        $this->phonenumber = '';
        $this->address = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:branches,name,NULL,id,deleted_at,NULL|string|min:2',
        'email' => 'required|email|unique:branches,email,NULL,id,deleted_at,NULL',
        'phonenumber' => 'required|unique:branches,phonenumber,NULL,id,deleted_at,NULL|digits:10',
        'address' => 'required|unique:branches,address,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
        $branch = new Branch;
        $branch->user_id = Auth::user()->id;
        $branch->name = $this->name;
        $branch->email = $this->email;
        $branch->phonenumber = $this->phonenumber;
        $branch->address = $this->address;
        $branch->save();

        $this->dispatchBrowserEvent('hide-branchModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Branch Created Successfully!!"
        ]);
        }
            catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while creating branch!!"
            ]);
        }
    }

    public function edit($id){
    $branch = Branch::find($id);
    $this->updateMode = true;
    $this->user_id = $branch->user_id;
    $this->name = $branch->name;
    $this->email = $branch->email;
    $this->phonenumber = $branch->phonenumber;
    $this->address = $branch->address;
    $this->branch_id = $branch->id;
    $this->dispatchBrowserEvent('show-branchEditModal');

    }



    public function update()
    {
        if ($this->branch_id) {
            try{
            $branch = Branch::find($this->branch_id);
            $branch->update([
                'name' => $this->name,
                'email' => $this->email,
                'phonenumber' => $this->phonenumber,
                'address' => $this->address,
            ]);

            $this->dispatchBrowserEvent('hide-branchEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Branch Updated Successfully!!"
            ]);
        }
            catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while updating branch!!"
            ]);
         }
        }
    }


    public function render()
    {
        $this->branches = Branch::latest()->get();
        return view('livewire.branches',[
            'branches' => $this->branches
        ]);
    }
}
