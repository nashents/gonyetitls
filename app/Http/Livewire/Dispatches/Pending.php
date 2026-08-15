<?php

namespace App\Http\Livewire\Dispatches;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Tyre;
use App\Models\Asset;
use App\Models\Account;
use Livewire\Component;
use App\Models\Dispatch;
use App\Models\Movement;
use App\Models\Inventory;
use App\Models\BillExpense;
use Livewire\WithPagination;
use App\Models\TicketExpense;
use App\Models\TyreAssignment;
use App\Models\TicketInventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\AuthorizationNotificationMail;

class Pending extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $from;
    public $to;
    public $search;
    public bool $notificationsOnly = false;
    protected $queryString = ['search', 'notificationsOnly' => ['as' => 'notifications', 'except' => false]];
    public $dispatch_filter = "created_at";
    private $dispatches;
    public $dispatch;
    public $dispatch_id;
    public $company;
    public $department;
    public $authorize;
    public $comments;


    public function mount($department){
        $this->department = $department;
        $this->company = Auth::user()->employee->company;
        $this->notificationsOnly = request()->boolean('notifications', false);
      
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

    public function tyreAssignment($dispatch_item){

        $dispatch = $dispatch_item->dispatch;
        $ticket = $dispatch?->ticket;
        $horse_id = $ticket?->horse_id;
        $vehicle_id = $ticket?->vehicle_id;
        $trailer_id = $ticket?->trailer_id;

        $assignment = new TyreAssignment;
        $assignment->user_id = Auth::user()->id;
        $assignment->tyre_id = $dispatch_item->tyre_id;
      
        if (isset($horse_id)) {
            $assignment->type = "Horse";
            $assignment->horse_id = $horse_id;
            $assignment->vehicle_id = null;
            $assignment->trailer_id = null;
        }elseif (isset($trailer_id)) {
            $assignment->type = "Trailer";
            $assignment->trailer_id = $trailer_id;
            $assignment->horse_id = null;
            $assignment->vehicle_id = null;
        }elseif (isset($vehicle_id)) {
            $assignment->type = "Vehicle";
            $assignment->vehicle_id = $vehicle_id;
            $assignment->horse_id = null;
            $assignment->trailer_id = null;
        }
        $assignment->starting_odometer = $ticket?->odometer;
        $assignment->date_fitted = date('Y-m-d');
        $assignment->current_mileage = $ticket?->odometer;
        $assignment->status = 1;
        $assignment->save();

        $movement = Movement::firstOrNew(['tyre_assignment_id' => $assignment->id]);
        $movement->dispatch_id = $dispatch->id;
        $movement->dispatch_item_id = $dispatch_item->id;
        $movement->user_id = $assignment->user_id;
        $movement->tyre_id = $assignment->tyre_id;
        
        if ($assignment->horse_id) {
            $movement->location = 'Horse';
            $movement->horse_id = $assignment->horse_id;
        } elseif ($assignment->vehicle_id) {
            $movement->location = 'Vehicle';
            $movement->vehicle_id = $assignment->vehicle_id;
        } elseif ($assignment->trailer_id) {
            $movement->location = 'Trailer';
            $movement->trailer_id = $assignment->vehicle_id;
        }
        
        $movement->current_mileage = $assignment->current_mileage;
        $movement->mileage_moved = $assignment->starting_odometer;
        $movement->date =   $assignment->date_fitted;
        $movement->save();

    }


    public function createTicketInventory($dispatch_item){
        $dispatch = $dispatch_item->dispatch;
        $ticket_inventory = new  TicketInventory;
        $ticket_inventory->dispatch_id = $dispatch->id;
        $ticket_inventory->dispatch_item_id = $dispatch_item->id;
        $ticket_inventory->ticket_id = $dispatch->ticket_id ?? null;
        $ticket_inventory->product_id = $dispatch_item->product_id ?? null;
        $ticket_inventory->inventory_id =  $dispatch_item->inventory_id ?? null;
        $ticket_inventory->tyre_id =  $dispatch_item->tyre_id ?? null;
        $ticket_inventory->vehicle_id = $dispatch->vehicle_id ?? null;
        $ticket_inventory->horse_id = $dispatch->horse_id ?? null;
        $ticket_inventory->trailer_id = $dispatch_item->dispatch->trailer_id ?? null;
        $ticket_inventory->qty =  $dispatch_item->qty;
        $ticket_inventory->currency_id = $dispatch_item->currency_id ?? null;
        $ticket_inventory->amount =  $dispatch_item->amount;
        $ticket_inventory->exchange_amount = $dispatch_item->exchange_amount;
        $ticket_inventory->exchange_rate = $dispatch_item->exchange_rate;
        $ticket_inventory->save();
    }

    public function createTicketExpense($dispatch_item){
        $dispatch = $dispatch_item->dispatch;
        $ticket_expense = new  TicketExpense;
        $ticket_expense->dispatch_id = $dispatch->id;
        $ticket_expense->ticket_id = $dispatch->ticket_id;
        $ticket_expense->currency_id =  $dispatch_item->currency_id;
        $ticket_expense->payment_method_id =  $dispatch_item->payment_method_id;
        $ticket_expense->vendor_id =  $dispatch->vendor_id;
        $ticket_expense->product_id =  $dispatch_item->product_id;
        $ticket_expense->qty =  $dispatch_item->qty;
        $ticket_expense->amount =  $dispatch_item->amount;
        $ticket_expense->tax_rate = $dispatch_item->tax_rate;
        $ticket_expense->tax_id = $dispatch_item->tax_id;
        $ticket_expense->subtotal = $dispatch_item->subtotal;
        $ticket_expense->subtotal_incl = $dispatch_item->subtotal_incl;
        $ticket_expense->exchange_rate = $dispatch_item->exchange_rate;
        $ticket_expense->exchange_amount = $dispatch_item->exchange_amount;
        $ticket_expense->save();
                    
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
            $dispatch->authorization_date = now();
            $dispatch->authorization_comments = $this->comments;
            $dispatch->save();

            $company =  Auth::user()->employee->company;
            $user = $dispatch->user;
            $email = $user?->email ?? null;
            $notification = "Dispatch Authorization";
            if($email){
                Mail::to($email)->send(new AuthorizationNotificationMail($company, $notification, $user, $dispatch));
            }

            if ($this->authorize == "approved") {

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
                        $bill->vendor_id = $dispatch->vendor_id;
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


                    foreach ($dispatch->dispatch_items as $dispatch_item) {

                        if ($dispatch_item->dispatch->department == "tyre") {
                           $this->tyreAssignment($dispatch_item);
                        }
                        if ($dispatch->dispatch_for == "inventory") {
                            $this->createTicketInventory($dispatch_item);
                        }elseif($dispatch->dispatch_for == "expenses"){
                            $this->createTicketExpense($dispatch_item);
                        }
                       
                        

                        if (in_array($dispatch->department,['inventory','tyre'])) {

                            $expense = new BillExpense;
                            $expense->bill_id = $bill->id;
                            $expense->currency_id = $bill->currency_id;
                            $expense->qty = 1;
                            $expense->amount = $dispatch_item->amount;
                            $expense->subtotal = $dispatch_item->amount;
                            $expense->subtotal_incl = $dispatch_item->amount;
                            $expense->inventory_id = $dispatch_item->inventory_id;
                            $expense->tyre_id = $dispatch_item->tyre_id;
                            $expense->asset_id = $dispatch_item->asset_id;
                            $expense->exchange_amount = $dispatch_item->exchange_amount;
                            $expense->exchange_rate = $dispatch_item->exchange_rate;
                            if ($account) {
                                $expense->account_id = $account->id;
                                $expense->account_type_id = optional($account->account_type)->id;
                            }
                        
                            $expense->save();

                        }

                            if ($dispatch_item->inventory_id) {
                                $item = Inventory::find($dispatch_item->inventory_id);
                            } elseif ($dispatch_item->asset_id) {
                                $item = Asset::find($dispatch_item->asset_id);
                            } elseif ($dispatch_item->tyre_id) {
                                $item = Tyre::find($dispatch_item->tyre_id);
                            } else {
                                continue;
                            }

                            if (!$item) {
                                continue;
                            }

                            // Reduce the balance / contents
                            $item->balance = max(0, (float)$item->balance - (float)$dispatch_item->qty);

                            if ($item->balance <= 0) {
                                $item->status = 0;   // optional: mark as exhausted/closed
                            }

                            $item->save();
 
                    }
                
                    $this->dispatchBrowserEvent('hide-authorizationModal');
                    $this->dispatchBrowserEvent('alert', [
                        'type' => 'success',
                        'message' => "Dispatch Approved & Item(s) Dispatched Successfully"
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
        $base = Dispatch::query()->with(['ticket','horse','vehicle','trailer','employee','department','branch'])
                    ->where('department',$this->department)
                    ->where('authorization','pending');
        if ($this->notificationsOnly) {
            $base->whereYear($this->dispatch_filter, now()->year);
        }else{
            $base->when(filled($this->from) && filled($this->to), function ($q) {
                $q->whereDate($this->dispatch_filter, '>=', $this->from)
                ->whereDate($this->dispatch_filter, '<=', $this->to);
            }, function ($q) {
                $q->whereMonth($this->dispatch_filter, Carbon::now()->month)
                ->whereYear($this->dispatch_filter, Carbon::now()->year);
            });
        }

               // Search filter (grouped to keep AND/OR logic correct)
            $base->when(filled($this->search), function ($q) {
                $term = '%'.$this->search.'%';

                $q->where(function ($qq) use ($term) {
                    $qq->where('dispatch_number', 'like', $term)
                    ->orWhere('authorization', 'like', $term)
                    ->orWhere('date', 'like', $term)
                    ->orWhereHas('ticket', function ($sub) use ($term) {
                        $sub->where('ticket_number', 'like', $term);
                    })
                    ->orWhereHas('horse', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term)
                        ->orWhere('fleet_number', 'like', $term);
                    })
                    ->orWhereHas('store', function ($sub) use ($term) {
                        $sub->where('name', 'like', $term);
                    })
                    ->orWhereHas('vehicle', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term)
                        ->orWhere('fleet_number', 'like', $term);
                    })
                    ->orWhereHas('trailer', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term)
                        ->where('fleet_number', 'like', $term);
                    })
                    ->orWhereHas('employee', function ($sub) use ($term) {
                        $sub->where(DB::raw("concat(name, ' ', surname)"), 'like', $term);
                    })
                    ->orWhereHas('dispatch_items.product', function ($sub) use ($term) {
                        $sub->where('product_number', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhere('identification_number', 'like', $term);
                    })
                    ->orWhereHas('dispatch_items.inventory', function ($sub) use ($term) {
                        $sub->where('inventory_number', 'like', $term)
                        ->orWhere('serial_number', 'like', $term);
                    })
                    ->orWhereHas('dispatch_items.tyre', function ($sub) use ($term) {
                        $sub->where('tyre_number', 'like', $term)
                        ->orWhere('serial_number', 'like', $term);
                    })
                    ->orWhereHas('dispatch_items.asset', function ($sub) use ($term) {
                        $sub->where('asset_number', 'like', $term)
                        ->orWhere('serial_number', 'like', $term);
                    });
                });
            });

            $dispatches = $base
                ->orderByDesc($this->dispatch_filter)
                ->paginate(10);

        return view('livewire.dispatches.pending',[
        'dispatches' => $dispatches
        ]);
    }
}
