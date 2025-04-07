<?php

namespace App\Http\Livewire\LoanTypes;

use Livewire\Component;
use App\Models\LoanType;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $loan_type_id;
    public $loan_types;
    public $name;
    public $updateMode;

    public function mount(){
      $this->loan_types = $loan_types = LoanType::latest()->get();
    }

    private function resetInputFields(){
        $this->name = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:loan_types,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
        $loan_type = new LoanType;
        $loan_type->user_id = Auth::user()->id;
        $loan_type->name = $this->name;
        $loan_type->save();
        $this->dispatchBrowserEvent('hide-loan_typeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Loan Type Created Successfully!!"
        ]);

        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while creating loan Type!!"
            ]);
        }
    }

    public function edit($id){
    $loan_type = LoanType::find($id);
    $this->updateMode = true;
    $this->user_id = $loan_type->user_id;
    $this->name = $loan_type->name;
    $this->loan_type_id = $loan_type->id;
    $this->dispatchBrowserEvent('show-loan_typeEditModal');

    }
    public function update()
    {
        if ($this->loan_type_id) {
            try{
            $loan_type = LoanType::find($this->loan_type_id);
            $loan_type->update([
                'name' => $this->name,
            ]);
            $this->dispatchBrowserEvent('hide-loan_typeEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Loan Type Updated Successfully!!"
            ]);


            }catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something went wrong while creating loan Type!!"
                ]);
            }
        }
    }
    public function render()
    {
        $this->loan_types = $loan_types = LoanType::latest()->get();
        return view('livewire.loan-types.index',[
            'loan_types' => $this->loan_types
        ]);
    }
}
