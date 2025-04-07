<?php

namespace App\Http\Livewire\Salaries;

use App\Models\Cargo;
use App\Models\Salary;
use Livewire\Component;
use App\Models\Allowance;
use App\Models\Deduction;
use App\Models\SalaryItem;
use App\Models\Destination;
use App\Models\LoadingPoint;
use App\Models\OffloadingPoint;


class Items extends Component
{
    public $salary;
    public $salary_id;
    public $salary_items;
    public $salary_item_id;
    public $deduction_id;
    public $currencies;
    public $currency_id;
    public $gross;
    public $net;
    public $basic;
    public $employees;
    public $employee_id;
    public $user_id;
    public $total_allowances;
    public $total_deductions;
    public $deductions;
    public $deduction_amount;
    public $allowances;
    public $allowance_amount;
    public $allowance_id;
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

    public $deductions_inputs = [];
    public $l = 1;
    public $m = 1;
    
    public function deductionsAdd($l)
    {
        $l = $l + 1;
        $this->l = $l;
        array_push($this->deductions_inputs ,$l);
    }
    
    public function deductionsRemove($l)
    {
        unset($this->deductions_inputs[$l]);
    }
    
private function resetInputFields(){
    $this->from = "" ;
}

    public function mount($id){
        $this->salary_id = $id;
        $this->salary = Salary::find($id);
        $this->allowances = Allowance::orderBy('name','asc')->get();
        $this->deductions = Deduction::where('name','asc')->get();
    }

public function store(){

    foreach ($this->salary_item_id as $key => $value) {
        $salary_item = new SalaryItem;
        $salary_item->salary_id = $this->salary_id;
        if (isset($this->salary_item_id[$key])) {
          $salary_item->salary_item_id = $this->salary_item_id[$key];
          $salary_item->amount = $this->amount[$key];
          $salary_item->save();
        }
    }

    $this->dispatchBrowserEvent('hide-salary_itemModal');
    $this->resetInputFields();
    $this->dispatchBrowserEvent('alert',[
        'type'=>'success',
        'message'=>"Salary Item(s) Added Successfully!!"
    ]);
   
}



    public function removeShow($salary_item_id){
        $this->salary_item = SalaryItem::find($salary_item_id);
        $this->vat = $this->salary->vat;
        $this->total = $this->salary->total;
        $this->subtotal = $this->salary->subtotal;
        $this->salary_items = $this->salary->salary_items;
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removesalarydetail(){ 

        $salary = $this->salary_item->salary;
        $this->deleted_detail_subtotal = $this->subtotal - $this->salary_item->freight;

        if ($this->vat != "") {
            $this->total = $this->deleted_detail_subtotal + ( $this->deleted_detail_subtotal * ($this->vat/100));
            $salary =  Salary::find($this->salary->id);
            $salary->total = $this->total;
            $salary->subtotal = $this->deleted_detail_subtotal;
            $salary->vat = $this->vat;
            $salary->update();
        }else{
            $this->total = $this->deleted_detail_subtotal;
            $salary =  Salary::find($this->salary->id);
            $salary->total = $this->total;
            $salary->subtotal = $this->deleted_detail_subtotal;
            $salary->vat = $this->vat;
            $salary->update();
        }
        $this->salary_item->delete();
        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"salary detail Deleted Successfully!!"
        ]);

   

    }

    public function edit($id){
     
        $this->salary_item = SalaryItem::find($id);
        $this->salary_item_id = $id;
        $this->freight = $this->salary_item->freight;
        $this->dispatchBrowserEvent('show-editsalary_itemModal');
    }

    public function update(){

      
        $salary_item = SalaryItem::find($this->salary_item_id);
        $salary_item->from = $this->from;
        $salary_item->to = $this->to;
        $salary_item->loading_point_id = $this->loading_point_id;
        $salary_item->offloading_point_id = $this->offloading_point_id;
        $salary_item->cargo_id = $this->cargo_id;
        $salary_item->weight = $this->weight;
        $salary_item->rate = $this->rate;
        $salary_item->freight = $this->freight;
        $salary_item->update();

        $salary = $salary_item->salary;
        $this->edited_detail_subtotal = $salary->salary_items->sum('freight');

        $this->dispatchBrowserEvent('hide-editsalary_itemModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Salary Detail Updated Successfully!!"
        ]);
    }

    public function render()
    {
        $this->salary_items = SalaryItem::where('salary_id',$this->salary_id)->get();
        return view('livewire.salaries.items',[
            'salary_items' => $this->salary_items
        ]);
    }
}
