<?php

namespace App\Http\Livewire\Dispatches;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Tyre;
use App\Models\Account;
use Livewire\Component;
use App\Models\Dispatch;
use App\Models\Inventory;
use App\Models\BillExpense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Pending extends Component
{

    public $dispatches;
    public $dispatch;
    public $dispatch_id;
    public $company;
    public $department;
    public $authorize;
    public $comments;


    public function mount($department){
        $this->department = $department;
        $this->company = Auth::user()->employee->company;
        $this->dispatches  = Dispatch::where('department',$department)->where('authorization','pending')->get();
    }

    public function authorize($id){
        $dispatch = Dispatch::find($id);
        $this->dispatch_id = $dispatch->id;
        $this->dispatch = $dispatch;
        $this->dispatchBrowserEvent('show-authorizationModal');
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

    public function update()
    {
        DB::transaction(function () {

            $dispatch = Dispatch::find($this->dispatch_id);

            if (!$dispatch) {
                throw new \Exception("Dispatch not found.");
            }

            $dispatch->authorized_by_id = Auth::id();
            $dispatch->authorization = $this->authorize;
            $dispatch->authorization_date = Carbon::today();
            $dispatch->authorization_comments = $this->comments;
            $dispatch->save();

            if ($this->authorize == "approved") {

                    if ($dispatch->department == "inventory" || $dispatch->department == "tyre" ) {

                        $account = Account::where('name', 'Repairs & Maintenance')->first();

                        $bill = new Bill;
                        $bill->user_id = Auth::id();
                        $bill->bill_number = $this->billNumber();
                        $bill->dispatch_id = $dispatch->id;
                        $bill->ticket_id = $dispatch->ticket_id;
                        $bill->employee_id = $dispatch->employee_id;
                        $bill->branch_id = $dispatch->branch_id;
                        $bill->department_id = $dispatch->department_id;
                        $bill->trailer_id = $dispatch->trailer_id;
                        $bill->vehicle_id = $dispatch->vehicle_id;
                        $bill->horse_id = $dispatch->horse_id;
                        $bill->bill_date = $dispatch->date;
                        $bill->currency_id = $dispatch->currency_id;
                        $bill->category = "Ticket Dispatch";
                        $bill->authorization = $this->authorize;
                        $bill->authorized_by_id = Auth::id();
                        $bill->to_be_paid = false;
                        
                        if ($account) {
                            $bill->account_id = $account->id;
                            $bill->account_type_id = optional($account->account_type)->id;
                        }
                        
                        $bill->total = $dispatch->total;
                        $bill->balance = $dispatch->total;
                        $bill->save();

                    }

                    foreach ($dispatch->dispatch_items as $item) {

                        if ($dispatch->department == "inventory" || $dispatch->department == "tyre" ) {

                            $expense = new BillExpense;
                            $expense->bill_id = $bill->id;
                            $expense->currency_id = $bill->currency_id;
                            $expense->qty = 1;
                            $expense->amount = $item->amount;
                            $expense->subtotal = $item->amount;
                            $expense->subtotal_incl = $item->amount;
                            $expense->inventory_id = $item->inventory_id;
                            $expense->tyre_id = $item->tyre_id;
                            $expense->asset_id = $item->asset_id;
                            $expense->exchange_amount = $item->exchange_amount;
                            $expense->exchange_rate = $item->exchange_rate;
                            if ($account) {
                                $expense->account_id = $account->id;
                                $expense->account_type_id = optional($account->account_type)->id;
                            }
                        
                            $expense->save();

                        }

                       
                        // Inventory updates
                        if ($item->inventory_id) {
                            $inventory = Inventory::find($item->inventory_id);
                            if ($inventory && is_numeric($inventory->balance) && is_numeric($item->weight)) {
                                $inventory->balance -= $item->weight;
                                if ($inventory->balance <= 0) $inventory->status = 0;
                                $inventory->save();
                            }
                        }

                        if ($item->asset_id) {
                            $asset = Asset::find($item->asset_id);
                            if ($asset && is_numeric($asset->balance) && is_numeric($item->weight)) {
                                $asset->balance -= $item->weight;
                                if ($asset->balance <= 0) $asset->status = 0;
                                $asset->save();
                            }
                        }

                        // Tyre status updates
                        if ($item->tyre_id) {
                            $tyre = Tyre::find($item->tyre_id);
                            if ($tyre) {
                                $tyre->status = 0;
                                $tyre->save();
                            }
                        }
                    }
                
                    $this->dispatchBrowserEvent('hide-authorizationModal');
                    $this->dispatchBrowserEvent('alert', [
                        'type' => 'success',
                        'message' => "Dispatch Approved Successfully"
                    ]);

                    if ($this->department == "tyre") {
                        return redirect()->route('tyre_dispatches.approved');
                    }elseif ($this->department == "inventory") {
                        return redirect()->route('inventory_dispatches.approved');
                    }elseif ($this->department == "asset") {
                        return redirect()->route('asset_dispatches.approved');
                    }
                    
                    //ticket ends here

            }

            // If rejected
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => "Dispatch Rejected Successfully"
            ]);

            if ($this->department == "tyre") {
                return redirect()->route('tyre_dispatches.rejected');
            }elseif ($this->department == "inventory") {
                return redirect()->route('inventory_dispatches.rejected');
            }elseif ($this->department == "asset") {
                return redirect()->route('asset_dispatches.rejected');
            }
        });
    }

    public function render()
    {
        return view('livewire.dispatches.pending');
    }
}
