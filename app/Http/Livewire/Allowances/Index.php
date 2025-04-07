<?php

namespace App\Http\Livewire\Allowances;

use Livewire\Component;
use App\Models\Allowance;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $allowances;
    public $calculate_by = "currency";
    public $calculate_on;
    public $description;
    public $name;
    public $amount;
    public $percentage;
    public $status;
    public $allowance_id;
    public $user_id;

    public function mount(){
        $this->allowances = Allowance::orderBy('name','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'calculate_by' => 'required',
        'name' => 'required|unique:allowances,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->calculate_by = "currency";
        $this->calculate_on = '';
        $this->name = '';
        $this->amount = '';
        $this->percentage = '';
        $this->description = '';
    }

    public function store(){
        try{
        $allowance = new Allowance;
        $allowance->user_id = Auth::user()->id;
        $allowance->name = $this->name;
        $allowance->calculate_by = $this->calculate_by;
        $allowance->calculate_on = $this->calculate_on;
        if ($this->calculate_by == "currency") {
            $allowance->amount = $this->amount;
            $allowance->percentage = Null;
        }elseif ($this->calculate_by == "percentage") {
            $allowance->amount = Null;
            $allowance->percentage =  $this->percentage;
        }
       
        $allowance->description = $this->description;
        $allowance->status =1;
        $allowance->save();

        $this->dispatchBrowserEvent('hide-allowanceModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Allowance Created Successfully!!"
        ]);


        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating allowance!!"
        ]);
    }
    }

    public function edit($id){
    $allowance = Allowance::find($id);
    $this->user_id = $allowance->user_id;
    $this->name = $allowance->name;
    $this->calculate_on = $allowance->calculate_on;
    $this->calculate_by = $allowance->calculate_by;
    $this->amount = $allowance->amount;
    $this->percentage = $allowance->percentage;
    $this->description = $allowance->description;
    $this->allowance_id = $allowance->id;
    $this->status = $allowance->status;
    $this->dispatchBrowserEvent('show-allowanceEditModal');

    }


    public function update()
    {
        if ($this->allowance_id) {
            try{
            $allowance = Allowance::find($this->allowance_id);
            $allowance->user_id = Auth::user()->id;
            $allowance->name = $this->name;
            $allowance->calculate_by = $this->calculate_by;
            $allowance->calculate_on = $this->calculate_on;
            if ($this->calculate_by == "currency") {
                $allowance->amount = $this->amount;
                $allowance->percentage = Null;
            }elseif ($this->calculate_by == "percentage") {
                $allowance->amount = Null;
                $allowance->percentage =  $this->percentage;
            }
            $allowance->description = $this->description;
            $allowance->status = $this->status;
            $allowance->update();

            $this->dispatchBrowserEvent('hide-allowanceEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Allowance Updated Successfully!!"
            ]);


            // return redirect()->route('allowances.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-allowanceEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating allowance!!"
            ]);
          }
        }
    }


    public function render()
    {
        $this->allowances = Allowance::orderBy('name','asc')->get();
        return view('livewire.allowances.index',[
            'allowances'=>   $this->allowances
        ]);
    }
}
