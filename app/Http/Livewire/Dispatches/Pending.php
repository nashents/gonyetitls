<?php

namespace App\Http\Livewire\Dispatches;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Tyre;
use App\Models\Asset;
use App\Models\Account;
use Livewire\Component;
use App\Models\Dispatch;
use App\Models\Inventory;
use App\Models\BillExpense;
use Livewire\WithPagination;
use App\Models\TicketExpense;
use App\Models\TicketInventory;
use App\Services\Accounting\InventoryJournalService;
use App\Services\Dispatches\TyreDispatchFitmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\AuthorizationNotificationMail;
use App\Http\Livewire\Concerns\HasStayOnPageAuthorization;

class Pending extends Component
{

    use WithPagination, HasStayOnPageAuthorization;
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
    public array $fitments = [];      // tyre dispatches: [dispatch_item_id => [fit_type, axle, position]]
    public array $fitUnitOptions = [];


    public function mount($department){
        $this->department = $department;
        $this->company = Auth::user()->employee->company;
        $this->notificationsOnly = request()->boolean('notifications', false);
      
    }

    public function authorize($id){
        $dispatch = Dispatch::find($id);
        $this->dispatch_id = $dispatch->id;
        $this->dispatch = $dispatch;
        $this->loadFitments($dispatch);
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

    private function fitmentService(): TyreDispatchFitmentService
    {
        return app(TyreDispatchFitmentService::class);
    }

    // Tyre dispatches are approved with a fitting position per tyre; older dispatches
    // without one get it filled in here.
    private function loadFitments(?Dispatch $dispatch): void
    {
        $this->fitments = [];
        $this->fitUnitOptions = [];

        if (!$dispatch || $dispatch->department != "tyre") {
            return;
        }

        $service = $this->fitmentService();
        $units = $service->unitsFor($dispatch);
        foreach ($units as $type => $id) {
            $this->fitUnitOptions[$type] = $service->unitLabel($type, $id) ?? $type;
        }

        foreach ($dispatch->dispatch_items()->with('tyre')->whereNotNull('tyre_id')->get() as $item) {
            $tyre = $item->tyre;
            $this->fitments[$item->id] = [
                'tyre' => $tyre ? trim(($tyre->tyre_number ?? '').($tyre->serial_number ? ' SN#: '.$tyre->serial_number : '')) : '',
                'fit_type' => $item->fit_type ?: (count($units) === 1 ? array_key_first($units) : null),
                'axle' => $item->axle,
                'position' => $item->position,
            ];
        }
    }

    private function tyreFitmentProblem(): ?string
    {
        $dispatch = Dispatch::find($this->dispatch_id);
        if (!$dispatch || $dispatch->department != "tyre") {
            return null;
        }

        $rows = [];
        foreach ($dispatch->dispatch_items()->whereNotNull('tyre_id')->get() as $item) {
            $fitment = $this->fitments[$item->id] ?? [];
            $rows[$item->id] = [
                'tyre_id' => $item->tyre_id,
                'fit_type' => $fitment['fit_type'] ?? null,
                'axle' => $fitment['axle'] ?? null,
                'position' => $fitment['position'] ?? null,
            ];
        }

        $errors = $this->fitmentService()->validate($rows, $this->fitmentService()->unitsFor($dispatch));
        // A unit is only needed when tyres are being fitted on approval.
        $fitting = collect($rows)->contains(fn ($row) => filled($row['axle']) || filled($row['position']));
        if (isset($errors['fit_unit'])) {
            if ($fitting) {
                return "This dispatch has no horse, trailer or vehicle to fit the tyres to. Clear the positions to approve without fitting.";
            }
            unset($errors['fit_unit']);
        }

        return $errors ? reset($errors) : null;
    }

    private function fitTyres(Dispatch $dispatch): void
    {
        $service = $this->fitmentService();

        foreach ($dispatch->dispatch_items()->with('tyre')->whereNotNull('tyre_id')->get() as $item) {
            $fitment = $this->fitments[$item->id] ?? [];
            $item->fit_type = ($fitment['fit_type'] ?? null) ?: null;
            $item->axle = ($fitment['axle'] ?? null) ?: null;
            $item->position = ($fitment['position'] ?? null) ?: null;
            $item->save();

            // No position: the tyre leaves stores unfitted and is fitted later in Tyre Assignments.
            if ($item->axle && $item->position) {
                $service->fit($dispatch, $item);
            }
        }
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
        // Checked up front: approval sends mail and posts a bill, so fail before any of that.
        if ($this->authorize == "approved" && ($message = $this->tyreFitmentProblem())) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => $message
            ]);
            return;
        }

        return DB::transaction(function () {

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

                    if ($dispatch->department == "tyre") {

                        $this->fitTyres($dispatch);

                    }


                    app(InventoryJournalService::class)->postDispatchBill($bill);

                    $this->dispatchBrowserEvent('hide-authorizationModal');
                    $this->dispatchBrowserEvent('alert', [
                        'type' => 'success',
                        'message' => "Dispatch Approved & Item(s) Dispatched Successfully"
                    ]);

                    if ($this->department == "tyre") {
                        return $this->redirectOrStay('tyre_dispatches.approved');
                    }elseif ($this->department == "inventory") {
                        return $this->redirectOrStay('inventory_dispatches.approved');
                    }elseif ($this->department == "asset") {
                        return $this->redirectOrStay('asset_dispatches.approved');
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
                return $this->redirectOrStay('tyre_dispatches.rejected');
            }elseif ($this->department == "inventory") {
                return $this->redirectOrStay('inventory_dispatches.rejected');
            }elseif ($this->department == "asset") {
                return $this->redirectOrStay('asset_dispatches.rejected');
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
                // Skip the default "this month" restriction while searching so matches
                // outside the current period (e.g. by product/inventory/tyre/asset #) aren't hidden.
                if (! filled($this->search)) {
                    $q->whereMonth($this->dispatch_filter, Carbon::now()->month)
                    ->whereYear($this->dispatch_filter, Carbon::now()->year);
                }
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
                    ->orWhereHas('dispatch_items.product.brand', function ($sub) use ($term) {
                        $sub->where('name', 'like', $term);
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
