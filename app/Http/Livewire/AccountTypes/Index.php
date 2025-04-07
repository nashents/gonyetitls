<?php

namespace App\Http\Livewire\AccountTypes;

use Livewire\Component;
use App\Models\AccountType;
use App\Models\AccountTypeGroup;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $account_types;
    public $group;
    public $name;
    public $account_type_id;
    public $account_type_group_id;
    public $account_type_groups;

    public function mount(){
        $this->account_types = AccountType::latest()->get();
        $this->account_type_groups = AccountTypeGroup::latest()->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'account_type_group_id' => 'required',
        'name' => 'required|unique:account_types,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->account_type_group_id = '';
        $this->name = '';
    }

 

    public function store(){
        try{
        $account_type = new AccountType;
        $account_type->user_id = Auth::user()->id;
        $account_type->name = $this->name;
        $account_type->account_type_group_id = $this->account_type_group_id;
        $account_type->save();

        $this->dispatchBrowserEvent('hide-account_typeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Account Type Created Successfully!!"
        ]);

        // return redirect()->route('account_types.index');

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating account type!!"
        ]);
    }
    }

    public function edit($id){
    $account_type = AccountType::find($id);
    $this->user_id = $account_type->user_id;
    $this->name = $account_type->name;
    $this->account_type_group_id = $account_type->account_type_group_id;
    $this->account_type_id = $account_type->id;
    $this->dispatchBrowserEvent('show-account_typeEditModal');

    }


    public function update()
    {
        if ($this->account_type_id) {
            try{
            $account_type = AccountType::find($this->account_type_id);
            $account_type->update([
                'user_id' => Auth::user()->id,
                'name' => $this->name,
                'account_type_group_id' => $this->account_type_group_id,
            ]);

            $this->dispatchBrowserEvent('hide-account_typeEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Account Type Updated Successfully!!"
            ]);


            // return redirect()->route('account_types.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-account_typeEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating account type!!"
            ]);
          }
        }
    }
    public function render()
    {
        $this->account_types = AccountType::latest()->get();
        return view('livewire.account-types.index',[
            'account_types' => $this->account_types
        ]);
    }
}
