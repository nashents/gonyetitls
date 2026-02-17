<?php

namespace App\Http\Livewire\TicketInventories;

use App\Models\Account;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Inventory;
use App\Models\InventoryDispatch;
use App\Models\InventoryRequisition;
use App\Models\Product;
use App\Models\TicketInventory;
use App\Models\Tyre;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    public $search_inventory;
    public $search_tyres;
    protected $queryString = ['search_inventory','search_tyres'];
    public $ticket;
    public $ticket_id;
    public $inventory_type = "spares";
    public $inventory_products;
    public $tyre_products;
    public $selectedProduct;
    public $inventories;
    public $inventory;
    public $tyre;
    public $selectedInventory;
    public $selectedTyre;
    protected $ticket_inventories;
    public $ticket_inventory_id;
    public $qty;
    public $amount;
    public $inventory_qty;
    public $weight;
    public $old_weight;
    public $subtotal;
    public $usage;
    public $previous_weight;
    public $tyres;
    public $vehicle_id;
    public $horse_id;
    public $currency_id;
    public $trailer_id;
    public $employee_ids = [];
    public $employees;
    public $user;
    public $employee;

 
    public function mount($ticket){

        // $this->resetPage();

        $this->ticket = $ticket;
      
        $this->inventories = collect();
        $this->tyres = collect();
       $this->user = Auth::user();
        $this->employee = $this->user->employee;
          $this->employees = $this->ticket->booking->employees;
        foreach ($this->employees as $employee) {
            $this->employee_ids[] = $employee->id;
        }
    }

    public function updatedSelectedProduct($id){
        if (!is_null($id)) {
            $this->inventories = Inventory::where('product_id',$id)->where('status',1)->where('balance','>',0)->get();
            $this->tyres = Tyre::where('product_id',$id)->where('status',1)->get();
        }
    }

    public function updatedSelectedInventory($id){
        if (isset($id)) {
           $this->inventory = Inventory::find($id);
        }
    }
    

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'selectedInventory' => 'required',
        'weight' => 'required',
    ];

    private function resetInputFields(){
        $this->selectedProduct = '';
        $this->selectedInventory = '';
        $this->weight = '';
    }

    public function billNumber(){

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

        $bill = Bill::latest()->orderBy('id','desc')->first();

        if (!$bill) {
            $bill_number =  $initials .'B'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $bill->id + 1;
            $bill_number =  $initials .'B'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $bill_number;


    }



    public function addProducts(){
        // try{


            $inventory = Inventory::find($this->selectedInventory);
            $tyre = Tyre::find($this->selectedTyre);

            $ticket_inventory = new  TicketInventory;
            $ticket_inventory->ticket_id = $this->ticket->id;

            if(isset($this->inventory_type) && $this->inventory_type == "spares"){
                $ticket_inventory->inventory_id =  $this->selectedInventory;
                $ticket_inventory->tyre_id =  Null;
            }elseif(isset($this->inventory_type) && $this->inventory_type == "tyres"){
                $ticket_inventory->tyre_id =  $this->selectedTyre;
                $ticket_inventory->inventory_id =  Null;
            }
          
            $ticket_inventory->vehicle_id = $this->ticket->vehicle_id;
         
            $ticket_inventory->horse_id = $this->ticket->horse_id;
            $ticket_inventory->trailer_id = $this->ticket->trailer_id;
            $ticket_inventory->qty =  1;
           




            if($this->inventory_type == "spares"){
                $ticket_inventory->weight = $this->weight ? $this->weight : 1;
                $ticket_inventory->measurement = $inventory->measurement ? $inventory->measurement : "Item";
                $ticket_inventory->currency_id = $inventory->currency_id;
               

                if (($this->weight && is_numeric($this->weight) && $this->weight > 0) && ($inventory->weight && is_numeric($inventory->weight) && $inventory->weight > 0) && ($inventory->subtotal_incl && is_numeric($inventory->subtotal_incl) && $inventory->subtotal_incl > 0 )) {
                    $amount = ($this->weight / $inventory->weight) * $inventory->subtotal_incl;
                    $ticket_inventory->amount = $amount ? $amount : 0;
                }

                $ticket_inventory->exchange_amount = $inventory->exchange_amount;
                $ticket_inventory->exchange_rate = $inventory->exchange_rate;
            }elseif($this->inventory_type == "tyres"){
                $ticket_inventory->currency_id = $tyre->currency_id;
                $ticket_inventory->amount = $tyre->subtotal_incl ? $tyre->subtotal_incl : 0;
                $ticket_inventory->exchange_amount = $tyre->exchange_amount;
                $ticket_inventory->exchange_rate = $tyre->exchange_rate;
            }
        
            $ticket_inventory->save();
            
            $bill = $ticket_inventory->bill;

            if (isset($bill)) {
                $bill_expense = new BillExpense;
                $bill_expense->bill_id = $bill->id;
                $bill_expense->currency_id = $bill->currency_id;
                if(isset($account)){
                    $account_type = $account->account_type;
                    $bill_expense->account_id = $account->id;
                    if (isset($account_type)) {
                        $bill_expense->account_type_id = $account_type->id;
                    }
                }
                $bill_expense->inventory_id = $this->selectedInventory;
                $bill_expense->qty = 1;
                $bill_expense->amount = $ticket_inventory->amount ? $ticket_inventory->amount: 0;
                $bill_expense->subtotal = $ticket_inventory->amount;
                $bill_expense->subtotal_incl = $ticket_inventory->amount;
                $bill_expense->save();
            }else{
                $bill = new Bill;
                $bill->user_id = Auth::user()->id;
                $bill->bill_number = $this->billNumber();
                $bill->ticket_inventory_id = $ticket_inventory->id;
                $bill->ticket_id = $ticket_inventory->ticket->id;
                $bill->horse_id = $ticket_inventory->horse_id;
                $bill->trailer_id = $ticket_inventory->trailer_id;
                $bill->vehicle_id = $ticket_inventory->vehicle_id;
                $bill->category = "Ticket";
                $bill->bill_date = date('d-m-Y');
                $account = Account::where('name','Repairs & Maintenance')->first();
                if(isset($account)){
                    $account_type = $account->account_type;
                    $bill->account_id = $account->id;
                    if (isset($account_type)) {
                        $bill->account_type_id = $account_type->id;
                    }
                }
                $bill->currency_id = $ticket_inventory->currency_id;
                $bill->total = $ticket_inventory->amount;
                $bill->balance = $ticket_inventory->amount;
                $bill->exchange_amount = $ticket_inventory->exchange_amount;
                $bill->exchange_rate = $ticket_inventory->exchange_rate;
                $bill->authorization = 'approved';
                $bill->to_be_paid = False;
                $bill->save();

                $bill_expense = new BillExpense;
                $bill_expense->bill_id = $bill->id;
                $bill_expense->currency_id = $bill->currency_id;
                if(isset($account)){
                    $account_type = $account->account_type;
                    $bill_expense->account_id = $account->id;
                    if (isset($account_type)) {
                        $bill_expense->account_type_id = $account_type->id;
                    }
                }
                $bill_expense->inventory_id = $this->selectedInventory;
                $bill_expense->qty = 1;
                $bill_expense->amount = $ticket_inventory->amount ? $ticket_inventory->amount: 0;
                $bill_expense->subtotal = $ticket_inventory->amount;
                $bill_expense->subtotal_incl = $ticket_inventory->amount;
                $bill_expense->save();
            }

           

            $requisition = new InventoryRequisition;
            $requisition->user_id = Auth::user()->id;
            $requisition->ticket_inventory_id = $ticket_inventory->id;
            if($this->inventory_type == "spares"){
                $requisition->inventory_id = $this->selectedInventory;
                $requisition->weight = $this->weight ? $this->weight : 0;
                $requisition->measurement = $inventory->measurement ? $inventory->measurement : "Item";
            }elseif($this->inventory_type == "tyres"){
                $requisition->tyre_id = $this->selectedTyre;
            }
            $requisition->ticket_id = $this->ticket->id;
            $requisition->vehicle_id = $this->ticket->vehicle_id;
            $requisition->horse_id = $this->ticket->horse_id;
            $requisition->trailer_id = $this->ticket->trailer_id;
            $requisition->qty = 1;
            $requisition->save();

         
            $dispatch = new InventoryDispatch;
            $dispatch->user_id = Auth::user()->id;
            $dispatch->inventory_requisition_id = $requisition->id;
            if($this->inventory_type == "spares"){
                $dispatch->inventory_id = $this->selectedInventory;
                $dispatch->measurement = $inventory->measurement ? $inventory->measurement : "Item";
                $dispatch->weight = $this->weight ? $this->weight : 0;
                $dispatch->part_number = $inventory->part_number;
            }elseif($this->inventory_type == "tyres"){
                $dispatch->tyre_id = $this->selectedTyre;
            }
            $dispatch->ticket_inventory_id = $ticket_inventory->id;
            $dispatch->horse_id = $this->ticket->horse_id;
            $dispatch->vehicle_id =$this->ticket->vehicle_id;
            $dispatch->trailer_id = $this->ticket->trailer_id;
           
            $dispatch->save();
            
            if($this->inventory_type == "spares"){
                if (($inventory->balance && is_numeric($inventory->balance) && $inventory->balance > 0) && ($this->weight && is_numeric($this->weight) && $this->weight > 0)) {
                    $inventory->balance = $inventory->balance - $this->weight;
                    if ($inventory->balance <= 0) {
                        $inventory->status = 0;
                    }
                    $inventory->update();
                }
            }elseif($this->inventory_type == "tyres"){
                $tyre->status = 0;
                $tyre->update();
            }
           
           
      


        $this->dispatchBrowserEvent('hide-ticket_inventoryModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item(s) Added Successfully!!"
        ]);

    //     }
    //     catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something goes wrong while creating cargo!!"
    //     ]);
    // }
    }

    public function edit($id){
        $ticket_inventory = TicketInventory::find($id);
   
        $inventory = Inventory::find($this->selectedInventory);
        $tyre = Tyre::find($this->selectedTyre);
        if (isset($inventory)) {
            $this->selectedProduct = $inventory->product_id;
        }elseif(isset($tyre)){
            $this->selectedProduct = $inventory->product_id;
        }
        $this->inventories = Inventory::where('status',1)->where('balance','>',0)->get();
        $this->tyres = Tyre::where('status',1)->get();
        $this->ticket_id = $ticket_inventory->ticket_id;
        $this->horse_id = $ticket_inventory->horse_id;
        $this->vehicle_id = $ticket_inventory->vehicle_id;
        
        if(isset($ticket_inventory->inventory_id)){
            $this->selectedInventory = $ticket_inventory->inventory_id;
            $this->inventory_type = "spares";
        }elseif($ticket_inventory->tyre_id){
            $this->selectedTyre = $ticket_inventory->tyre_id;
            $this->inventory_type = "tyres";
        }
        $this->currency_id = $ticket_inventory->currency_id;
        $this->trailer_id = $ticket_inventory->trailer_id;
        $this->previous_weight = $ticket_inventory->weight;
        $this->weight = $ticket_inventory->weight;
        $this->old_weight = $ticket_inventory->weight;
        $this->ticket_inventory_id = $ticket_inventory->id;
        $this->dispatchBrowserEvent('show-ticket_inventoryEditModal');
    }

    public function update(){

        $inventory = Inventory::find($this->selectedInventory);
        $tyre = Tyre::find($this->selectedTyre);

        $ticket_inventory =  TicketInventory::find($this->ticket_inventory_id);
        $ticket_inventory->ticket_id = $this->ticket->id;
        $ticket_inventory->vehicle_id =$this->ticket->vehicle_id;
        $ticket_inventory->horse_id = $this->ticket->horse_id;
        $ticket_inventory->trailer_id =$this->ticket->trailer_id;
        $ticket_inventory->qty =  1;
     
        if($this->inventory_type == "spares"){
            $ticket_inventory->inventory_id =  $this->selectedInventory;
            $ticket_inventory->tyre_id =  Null;
            $ticket_inventory->weight = $this->weight ? $this->weight : 0;
            $ticket_inventory->measurement = $inventory->measurement ? $inventory->measurement : "Item";
            $ticket_inventory->currency_id = $inventory->currency_id;
        if ((isset($this->weight) && is_numeric($this->weight && $this->weight > 0)) && (isset($inventory->weight) && is_numeric($inventory->weight) && $inventory->weight > 0) && (isset($inventory->subtotal_incl) && is_numeric($inventory->subtotal_incl) && $inventory->subtotal_incl > 0 )) {
            $amount = ($this->weight / $inventory->weight) * $inventory->subtotal_incl;
            $ticket_inventory->amount = $amount ? $amount : 0;

            }
        }elseif($this->inventory_type == "tyres"){
            $ticket_inventory->tyre_id =  $this->selectedTyre;
            $ticket_inventory->inventory_id =  Null;
            $ticket_inventory->currency_id = $tyre->currency_id;
            $ticket_inventory->amount = $tyre->subtotal_incl ? $tyre->subtotal_incl : 0;
        }
       
        $ticket_inventory->update();

        $bill =  $ticket_inventory->bill;
        if(isset($bill)){
            $bill->user_id = Auth::user()->id;
            $bill->bill_number = $this->billNumber();
            $bill->ticket_inventory_id = $ticket_inventory->id;
            $bill->horse_id = $ticket_inventory->horse_id;
            $bill->trailer_id = $ticket_inventory->trailer_id;
            $bill->vehicle_id = $ticket_inventory->vehicle_id;
            $bill->category = "Ticket";
            $bill->bill_date = date('d-m-Y');
            $account = Account::where('name','Repairs & Maintenance')->first();
            if(isset($account)){
                $account_type = $account->account_type;
                $bill->account_id = $account->id;
                if (isset($account_type)) {
                    $bill->account_type_id = $account_type->id;
                }
            }
            $bill->currency_id = $ticket_inventory->currency_id;
            $bill->total = $ticket_inventory->amount;
            $bill->balance = $ticket_inventory->amount;
            $bill->exchange_amount = $ticket_inventory->exchange_amount;
            $bill->exchange_rate = $ticket_inventory->exchange_rate;
            $bill->to_be_paid = False;
            $bill->authorization = 'approved';
            $bill->update();
    
            $bill_expense = $bill->bill_expenses->first();
    
            if(isset($bill_expense)){
            $bill_expense = new BillExpense;
            $bill_expense->bill_id = $bill->id;
            $bill_expense->currency_id = $bill->currency_id;
            if(isset($account)){
                $account_type = $account->account_type;
                $bill_expense->account_id = $account->id;
                if (isset($account_type)) {
                    $bill_expense->account_type_id = $account_type->id;
                }
            }
            $bill_expense->inventory_id = $this->selectedInventory;
            $bill_expense->qty = 1;
            $bill_expense->amount = $ticket_inventory->amount ? $ticket_inventory->amount: 0;
            $bill_expense->subtotal = $ticket_inventory->amount;
            $bill_expense->subtotal_incl = $ticket_inventory->amount;
            $bill_expense->update();
            }
        }
   

        $requisition = InventoryRequisition::find($ticket_inventory->inventory_requisition->id);
        $requisition->user_id = Auth::user()->id;
        $requisition->ticket_inventory_id = $ticket_inventory->id;
            if($this->inventory_type == "spares"){
                $requisition->inventory_id = $this->selectedInventory;
                $requisition->weight = $this->weight ? $this->weight : 0;
                $requisition->measurement = $inventory->measurement ? $inventory->measurement : "Item";
            }elseif($this->inventory_type == "tyres"){
                $requisition->tyre_id = $this->selectedTyre;
            }
            $requisition->ticket_id = $this->ticket->id;
            $requisition->vehicle_id =$this->ticket->vehicle_id;
            $requisition->horse_id = $this->ticket->horse_id;
            $requisition->trailer_id =$this->ticket->trailer_id;
            $requisition->qty = 1;
        $requisition->update();
     
        $dispatch = InventoryDispatch::find($ticket_inventory->inventory_dispatch->id);
       
        $dispatch->inventory_requisition_id = $requisition->id;
        if($this->inventory_type == "spares"){
            $dispatch->inventory_id = $this->selectedInventory;
            $dispatch->measurement = $inventory->measurement ? $inventory->measurement : "Item";
            $dispatch->weight = $this->weight ? $this->weight : 0;
            $dispatch->part_number = $inventory->part_number;
        }elseif($this->inventory_type == "tyres"){
            $dispatch->tyre_id = $this->selectedTyre;
        }
        $dispatch->ticket_inventory_id = $ticket_inventory->id;
        $dispatch->horse_id = $this->ticket->horse_id;
        $dispatch->vehicle_id =$this->ticket->vehicle_id;
        $dispatch->trailer_id = $this->ticket->trailer_id;
        $dispatch->update();
        
        if($this->inventory_type == "spares"){
            if ((isset($inventory->balance) && is_numeric($inventory->balance) && $inventory->balance > 0) && (isset($this->weight) && is_numeric($this->weight) && $this->weight > 0)) {
                $inventory->balance = $inventory->balance - $this->weight;
                if ($inventory->balance <= 0) {
                    $inventory->status = 0;
                }
                $inventory->update();
            }
        }elseif($this->inventory_type == "tyres"){
            $tyre->status = 0;
            $tyre->update();
        }
       
        $this->dispatchBrowserEvent('hide-ticket_inventoryEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item(s) Updated Successfully!!"
        ]);
    }

    // private function updatingSearch()
    // {
    //     $this->resetPage();
    // }


    public function render()
    {
    
        $this->ticket_inventories = TicketInventory::where('ticket_id', $this->ticket->id)->latest()->paginate(10);
        return view('livewire.ticket-inventories.index',[
            'ticket_inventories' => $this->ticket_inventories,
        ]);
    }
}
