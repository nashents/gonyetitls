<?php

namespace App\Http\Livewire\WasteCollections;

use App\Models\Employee;
use App\Models\UnitsOfMeasure;
use App\Models\WasteCollection;
use App\Models\WasteCollectionItem;
use App\Models\WasteReceptacle;
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
    public $collection_filter;

    protected $waste_collections;
    public $employees;

  
    public $waste_types;
    public $waste_collection_items;

    public $current_date = [];
    public $current_waste_type_id = [];
    public $current_description = [];
    public $current_qty = [];
    public $current_balance = [];
    public $current_unit_of_measure = [];
    public $current_waste_receptacle_id = [];
    public $current_collected_from = [];

    public $date = [];
    public $waste_type_id = [];
    public $description = [];
    public $qty = [];
    public $unit_of_measure = [];
    public $waste_receptacle_id = [];
    public $collected_from = [];
 
    public $waste_collection_id;
    public $waste_collection;

    public $unit_of_measures;
    public $waste_receptacles;

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
        $this->collected_from = [];
        $this->qty = [];
        $this->waste_receptacle_id = [];
        $this->inputs = [];
        $this->unit_of_measure = [];
        $this->description = [];
        $this->waste_type_id = [];
    }


    public function mount(){
        $this->waste_types = WasteType::orderBy('name','asc')->get();
        $this->waste_receptacles = WasteReceptacle::orderBy('name','asc')->get();
        $this->unit_of_measures = UnitsOfMeasure::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
     
    }

    public function waste_collectionNumber(){

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

            $last_waste_collection_id = WasteCollection::latest()->pluck('id')->first();

        if (!$last_waste_collection_id) {
            $waste_collection_number =  $initials .'WC'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $waste_collection_number = $last_waste_collection_id + 1;
            $waste_collection_number =  $initials .'WC'. str_pad($waste_collection_number, 5, "0", STR_PAD_LEFT);
        }

        return  $waste_collection_number;


    }

    public function store(){

     DB::transaction(function () {

        $waste_collection = new WasteCollection;
        $waste_collection->user_id = Auth::user()->id;
        $waste_collection->waste_collection_number = $this->waste_collectionNumber();
        $waste_collection->save();

        if ($this->waste_type_id) {
            foreach ($this->waste_type_id as $key => $typeId) {
               $waste_collection_item = new WasteCollectionItem;
               $waste_collection_item->waste_collection_id = $waste_collection->id;
               $waste_collection_item->waste_type_id = $typeId;
               $waste_collection_item->description = $this->description[$key] ?? Null;
               $waste_collection_item->qty = $this->qty[$key] ?? Null;
               $waste_collection_item->balance = $this->qty[$key] ?? Null;
               $waste_collection_item->unit_of_measure = $this->unit_of_measure[$key] ?? Null;
               $waste_collection_item->collected_from = $this->collected_from[$key] ?? Null;
               $waste_collection_item->waste_receptacle_id = $this->waste_receptacle_id[$key] ?? Null;
               $waste_collection_item->date = $this->date[$key] ?? Null;
               $waste_collection_item->save();
            }
        }

         $this->dispatchBrowserEvent('hide-waste_collectionModal');
          $this->resetInputFields();
          $this->dispatchBrowserEvent('alert',[
              'type'=>'success',
              'message'=>"Waste Collection Record Created Successfully!!"
          ]);

     });
    }

     public function refresh($category){

        if($category == "waste_types"){
            $this->waste_types = WasteType::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Waste Types Refreshed Successfully!!."
            ]);
        }
     }

    public function edit($id){
        $waste_collection = WasteCollection::find($id);
        $this->waste_collection_id = $id;
        $this->waste_collection_items = $waste_collection->waste_collection_items;
        if ($this->waste_collection_items) {
            foreach ($this->waste_collection_items as $item) {
                $this->current_date[] = $item->date;
                $this->current_unit_of_measure[] = $item->unit_of_measure;
                $this->current_waste_receptacle_id[] = $item->waste_receptacle_id;
                $this->current_waste_type_id[] = $item->waste_type_id;
                $this->current_description[] = $item->description;
                $this->current_qty[] = $item->qty;
                $this->current_balance[] = $item->balance;
                $this->current_collected_from[] = $item->collected_from;
            }
        }

        $this->dispatchBrowserEvent('show-waste_collectionEditModal');
    }


    public function update(){

     DB::transaction(function () {

        $waste_collection =  WasteCollection::find($this->waste_collection_id);
        $waste_collection->user_id = Auth::user()->id;
        $waste_collection->waste_collection_number = $this->waste_collectionNumber();
        $waste_collection->save();

        if ($this->waste_type_id) {
            foreach ($this->waste_type_id as $key => $typeId) {
               $waste_collection_item = new WasteCollectionItem;
               $waste_collection_item->waste_collection_id = $waste_collection->id;
               $waste_collection_item->waste_type_id = $typeId;
               $waste_collection_item->description = $this->description[$key] ?? Null;
               $waste_collection_item->qty = $this->qty[$key] ?? Null;
               $waste_collection_item->balance = $this->qty[$key] ?? Null;
               $waste_collection_item->unit_of_measure = $this->unit_of_measure[$key] ?? Null;
               $waste_collection_item->collected_from = $this->collected_from[$key] ?? Null;
               $waste_collection_item->waste_receptacle_id = $this->waste_receptacle_id[$key] ?? Null;
               $waste_collection_item->date = $this->date[$key] ?? Null;
               $waste_collection_item->save();
            }
        }
        if ($this->waste_collection_items) {
            foreach ($this->waste_collection_items as $key => $item) {
               $waste_collection_item = WasteCollectionItem::find($item->id);
               $waste_collection_item->waste_type_id =  $this->current_waste_type_id[$key] ?? Null;
               $waste_collection_item->description = $this->current_description[$key] ?? Null;
               $waste_collection_item->qty = $this->current_qty[$key] ?? Null;
               $waste_collection_item->balance = $this->current_balance[$key] ?? Null;
               $waste_collection_item->unit_of_measure = $this->current_unit_of_measure[$key] ?? Null;
               $waste_collection_item->collected_from = $this->current_collected_from[$key] ?? Null;
               $waste_collection_item->waste_receptacle_id = $this->current_waste_receptacle_id[$key] ?? Null;
               $waste_collection_item->date = $this->current_date[$key] ?? Null;
               $waste_collection_item->update();
            }
        }

         $this->dispatchBrowserEvent('hide-waste_collectionEditModal');
          $this->resetInputFields();
          $this->dispatchBrowserEvent('alert',[
              'type'=>'success',
              'message'=>"Waste Collection Record Updated Successfully!!"
          ]);

     });
    }
    public function render()
    {
        $query = WasteCollection::query()->with('waste_collection_items');
        $waste_collections = $query->orderBy('created_at','desc')->paginate(10);
        return view('livewire.waste-collections.index',[
            'waste_collections' => $waste_collections
        ]);
    }
}
