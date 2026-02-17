<?php

namespace App\Http\Livewire\Tickets;

use App\Models\UnitsOfMeasure;
use App\Models\WasteCollection;
use App\Models\WasteCollectionItem;
use App\Models\WasteReceptacle;
use App\Models\WasteType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class WasteCollections extends Component
{

   use WithPagination;
    public $ticket;
    public $waste_types;
    public $waste_collection_items;
    public $date = [];
    public $waste_type_id = [];
    public $collected_from = [];
    public $description = [];
    public $qty = [];
    public $unit_of_measure = [];
    public $waste_receptacle_id = [];
    public $selectedEmployee = [];
    
    public $current_date = [];
    public $current_waste_type_id = [];
    public $current_collected_from = [];
    public $current_description = [];
    public $current_qty = [];
    public $current_balance = [];
    public $current_unit_of_measure = [];
    public $current_waste_receptacle_id = [];
    public $current_selectedEmployee = [];
 
    public $waste_collection_id;
    public $waste_collection;
    private $waste_collections;
     public $unit_of_measures;
     public $waste_receptacles;
      public $employee_ids = [];
    public $employees;
    public $user;
    public $employee;

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

    public function mount($ticket){
        $this->ticket = $ticket;
        $this->waste_types = WasteType::orderBy('name','asc')->get();
        $this->waste_receptacles = WasteReceptacle::orderBy('name','asc')->get();
        $this->unit_of_measures = UnitsOfMeasure::orderBy('name','asc')->get();
         $this->user = Auth::user();
        $this->employee = $this->user->employee;
        $this->employees = $this->ticket->booking->employees;
        foreach ($this->employees as $employee) {
            $this->employee_ids[] = $employee->id;
        }
    }
    private function resetWasteCollectionInputFields(){
        $this->date = [];
        $this->selectedEmployee = [];
        $this->qty = [];
        $this->waste_receptacle_id = [];
        $this->inputs = [];
        $this->i = 1;
        $this->unit_of_measure = [];
        $this->description = [];
        $this->waste_type_id = [];
        $this->collected_from = [];
      
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

    public function waste_collection_store(){

     DB::transaction(function () {
        
        $waste_collection = new WasteCollection;
        $waste_collection->user_id = Auth::user()->id;
        $waste_collection->ticket_id = $this->ticket->id;
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
               $waste_collection_item->waste_receptacle_id = $this->waste_receptacle_id[$key] ?? Null;
               $waste_collection_item->collected_from = $this->collected_from[$key] ?? Null;
               $waste_collection_item->date = $this->date[$key] ?? Null;
               $waste_collection_item->save();
            }
        }

         $this->dispatchBrowserEvent('hide-waste_collectionModal');
          $this->resetWasteCollectionInputFields();
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

    public function waste_collection_edit($id){
        $waste_collection = WasteCollection::find($id);
        $this->waste_collection_id = $id;
        $this->waste_collection_items = $waste_collection->waste_collection_items;
        if ($this->waste_collection_items) {
            foreach ($this->waste_collection_items as $item) {
                $this->current_date[] = $item->date;
                $this->current_unit_of_measure[] = $item->unit_of_measure;
                $this->current_waste_receptacle_id[] = $item->waste_receptacle_id;
                $this->current_waste_type_id[] = $item->waste_type_id;
                $this->current_collected_from[] = $item->collected_from;
                $this->current_description[] = $item->description;
                $this->current_qty[] = $item->qty;
                $this->current_balance[] = $item->balance;
            }
        }

        $this->dispatchBrowserEvent('show-waste_collectionEditModal');
    }


    public function waste_collection_update(){

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
          $this->resetWasteCollectionInputFields();
          $this->dispatchBrowserEvent('alert',[
              'type'=>'success',
              'message'=>"Waste Collection Record Updated Successfully!!"
          ]);

     });
    }



    public function render()
    {
        $query = WasteCollection::query()->where('ticket_id', $this->ticket->id)->with('waste_collection_items');
        $waste_collections = $query->orderBy('created_at','desc')->paginate(10);
        return view('livewire.tickets.waste-collections',[
            'waste_collections' => $waste_collections
        ]);
    }
}
