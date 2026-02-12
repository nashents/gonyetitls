<?php

namespace App\Http\Livewire\WasteDisposals;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\WasteDisposal;
use App\Models\WasteDisposalItem;
use App\Models\WasteType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads;

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $disposal_filter;

    protected $waste_disposals;
    public $currencies;
    public $currency_id;
    public $customers;
    public $customer_id;
    public $movement = "Disposal";
    public $employees;
    public $selectedEmployee;
    public $date;
    public $waste_types;
    public $waste_disposal_items;
 

   
    public $waste_type_id = [];
    public $description = [];
    public $qty = [];
    public $use = [];
    public $amount = [];
    public $unit_of_measure = [];
    public $waste_receptacle = [];
   
    public $current_waste_type_id = [];
    public $current_description = [];
    public $current_qty = [];
    public $current_use = [];
    public $current_amount = [];
    public $current_unit_of_measure = [];
    public $current_selectedEmployee = [];
 
    public $waste_disposal_id;
    public $waste_disposal;

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

     private function resetInputFields(){
        $this->date = [];
        $this->selectedEmployee = [];
        $this->qty = [];
        $this->waste_receptacle = [];
        $this->inputs = [];
        $this->unit_of_measure = [];
        $this->description = [];
        $this->waste_type_id = [];
    }


    public function mount(){
        $this->waste_types = WasteType::orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
     
    }

    public function waste_disposalNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

            $last_waste_disposal_id = WasteDisposal::latest()->pluck('id')->first();

        if (!$last_waste_disposal_id) {
            $waste_disposal_number =  $initials .'WD'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $waste_disposal_number = $last_waste_disposal_id + 1;
            $waste_disposal_number =  $initials .'WD'. str_pad($waste_disposal_number, 5, "0", STR_PAD_LEFT);
        }

        return  $waste_disposal_number;


    }

    public function store(){

     DB::transaction(function () {

        $waste_disposal = new WasteDisposal;
        $waste_disposal->user_id = Auth::user()->id;
        $waste_disposal->waste_disposal_number = $this->waste_disposalNumber();
        $waste_disposal->employee_id = $this->selectedEmployee;
        $waste_disposal->date = $this->date;
        $waste_disposal->currency_id = $this->currency_id;
        $waste_disposal->customer_id = $this->customer_id;
        $waste_disposal->movement = $this->movement;
        $waste_disposal->save();

        if ($this->waste_type_id) {
            foreach ($this->waste_type_id as $key => $typeId) {
               $waste_disposal_item = new WasteDisposalItem;
               $waste_disposal_item->waste_disposal_id = $waste_disposal->id;
               $waste_disposal_item->waste_type_id = $typeId;
               $waste_disposal_item->description = $this->description[$key] ?? Null;
               $waste_disposal_item->qty = $this->qty[$key] ?? Null;
               $waste_disposal_item->unit_of_measure = $this->unit_of_measure[$key] ?? Null;
               $waste_disposal_item->use = $this->use[$key] ?? Null;
               $waste_disposal_item->amount = $this->amount[$key] ?? Null;
               $waste_disposal_item->currency_id = $this->currency_id;
               $waste_disposal_item->save();
            }
        }

         $this->dispatchBrowserEvent('hide-waste_disposalModal');
          $this->resetInputFields();
          $this->dispatchBrowserEvent('alert',[
              'type'=>'success',
              'message'=>"Waste disposal Record Created Successfully!!"
          ]);

     });
    }

      public function edit($id){
        $waste_disposal = WasteDisposal::find($id);
        $this->waste_disposal_id = $id;
        $this->selectedEmployee = $waste_disposal->employee_id;
        $this->movement = $waste_disposal->movement;
        $this->date = $waste_disposal->date;
        $this->currency_id = $waste_disposal->currency_id;
        $this->customer_id = $waste_disposal->customer_id;
        $this->waste_disposal_items = $waste_disposal->waste_disposal_items;
        if ($this->waste_disposal_items) {
            foreach ($this->waste_disposal_items as $item) {
                $this->current_amount[] = $item->amount;
                $this->current_unit_of_measure[] = $item->unit_of_measure;
                $this->current_waste_type_id[] = $item->waste_type_id;
                $this->current_description[] = $item->description;
                $this->current_qty[] = $item->qty;
                $this->current_selectedEmployee[] = $item->collected_by_id;
            }
        }

        $this->dispatchBrowserEvent('show-waste_disposalEditModal');
    }

     public function refresh($category){

        if($category == "tracking_groups"){
            $this->waste_types = WasteType::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Waste Types Refreshed Successfully!!."
            ]);
        }
     }
       

    public function update(){

     DB::transaction(function () {

        $waste_disposal = WasteDisposal::find($this->waste_disposal_id);
        $waste_disposal->user_id = Auth::user()->id;
        $waste_disposal->waste_disposal_number = $this->waste_disposalNumber();
        $waste_disposal->employee_id = $this->selectedEmployee;
        $waste_disposal->date = $this->date;
        $waste_disposal->currency_id = $this->currency_id;
        $waste_disposal->customer_id = $this->customer_id;
        $waste_disposal->movement = $this->movement;
        $waste_disposal->update();

        if ($this->waste_disposal_items) {
            foreach ($this->waste_disposal_items as $key => $item) {
               $waste_disposal_item =  WasteDisposalItem::find($item->id);
               $waste_disposal_item->waste_disposal_id = $waste_disposal->id;
               $waste_disposal_item->waste_type_id = $this->current_waste_type_id[$key] ?? Null;
               $waste_disposal_item->description = $this->current_description[$key] ?? Null;
               $waste_disposal_item->qty = $this->current_qty[$key] ?? Null;
               $waste_disposal_item->unit_of_measure = $this->current_unit_of_measure[$key] ?? Null;
               $waste_disposal_item->use = $this->current_use[$key] ?? Null;
               $waste_disposal_item->amount = $this->current_amount[$key] ?? Null;
               $waste_disposal_item->currency_id = $this->currency_id;
               $waste_disposal_item->update();
            }
        }
      
        if ($this->waste_type_id) {
            foreach ($this->waste_type_id as $key => $typeId) {
               $waste_disposal_item = new WasteDisposalItem;
               $waste_disposal_item->waste_disposal_id = $waste_disposal->id;
               $waste_disposal_item->waste_type_id = $typeId;
               $waste_disposal_item->description = $this->description[$key] ?? Null;
               $waste_disposal_item->qty = $this->qty[$key] ?? Null;
               $waste_disposal_item->unit_of_measure = $this->unit_of_measure[$key] ?? Null;
               $waste_disposal_item->use = $this->use[$key] ?? Null;
               $waste_disposal_item->amount = $this->amount[$key] ?? Null;
               $waste_disposal_item->currency_id = $this->currency_id;
               $waste_disposal_item->save();
            }
        }

         $this->dispatchBrowserEvent('hide-waste_disposalEditModal');
          $this->resetInputFields();
          $this->dispatchBrowserEvent('alert',[
              'type'=>'success',
              'message'=>"Waste disposal Record Updated Successfully!!"
          ]);

     });
    }


    public function render()
    {
        $query = WasteDisposal::query()->with('waste_disposal_items');
        $waste_disposals = $query->orderBy('created_at','desc')->paginate(10);
        return view('livewire.waste-disposals.index',[
            'waste_disposals' => $waste_disposals
        ]);
    }
}
