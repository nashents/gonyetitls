<?php

namespace App\Http\Livewire\Trips;

use App\Models\Trip;
use App\Models\Expense;
use Livewire\Component;
use App\Models\CashFlow;
use App\Models\Currency;
use App\Models\Allowance;
use App\Models\TripExpense;
use App\Models\PaymentMethod;
use App\Models\AllowanceDriver;
use App\Models\ExpenseCategory;
use App\Models\IntegrationMapping;
use App\Models\Vendor;
use App\Services\Accounting\TripExpenseJournalService;
use App\Services\Sage\Concerns\ResolvesSageIntegration;
use App\Services\Sage\SageIntegration;
use App\Services\Sage\SageRequisitionService;
use App\Services\Sage\SageSyncService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Expenses extends Component
{
    use ResolvesSageIntegration;

    public $trip;
    public $trip_id;
    public $user_id;
    public $expenses;
    public $trip_expense_type ;
    public $allowances;
    public $payment_methods;
    public $payment_method_id;
    public $category;
    public $selectedExpense;
    public $selectedAllowance;
    public $trip_expenses;
    public $trip_expense_id;
    public $edit;
    public $exchange_rate;
    public $exchange_amount;
    public $turnover;
    public $cost_of_sales;
    public $net_profit;
    public $trip_expense;



    public $name;
    public $amount;
    public $currencies;
    public $selectedCurrency;
    public $selected_currency;
    public $vendors;
    public $selectedVendor;
    public $visible_on_trip_sheet;

    public $total_customer_expenses;
    public $total_transporter_expenses;
    public $total_expenses;


    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        $this->trip_expense_type[$i] = "expense";
        $this->visible_on_trip_sheet[$i] = true;
        array_push($this->inputs ,$i);
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
        unset($this->trip_expense_type[$i]);
        unset($this->visible_on_trip_sheet[$i]);
    }

    public function addExpense()
    {
        $index = count($this->inputs); // Get new index
        $this->inputs[] = $index; // Add new input field index
        $this->trip_expense_type[$index] = "expense"; // Default each new entry to 'expense'
        $this->visible_on_trip_sheet[$index] = true;
    }

    private function resetInputFields(){
        $this->inputs = [];
        $this->trip_expense_type = Null ;
        $this->selectedExpense = Null ;
        $this->selectedAllowance = Null ;
        $this->exchange_amount = Null ;
        $this->exchange_rate = Null ;
        $this->amount = Null;
        $this->edit = Null;
        $this->selectedCurrency = Null;
        $this->selectedVendor = Null;
        $this->visible_on_trip_sheet = Null;
        $this->total_customer_expenses = 0;
        $this->total_transporter_expenses = 0;
        $this->total_expenses = 0;

    }

    public function mount($trip){

    $this->trip_expense_type[0] = "expense";
    $this->visible_on_trip_sheet[0] = true;
    foreach ($this->inputs as $index) {
        $this->trip_expense_type[$index] = $this->trip_expense_type[$index] ?? 'expense';
        $this->visible_on_trip_sheet[$index] = $this->visible_on_trip_sheet[$index] ?? true;
    }

    $this->trip = $trip;
    $this->trip_id = $trip->addid;
    $this->currencies = Currency::orderBy('name')->get();
    $this->vendors = Vendor::orderBy('name')->get();
    $this->allowances = Allowance::orderBy('name')->get();
    $this->payment_methods = PaymentMethod::orderBy('name')->get();
    $this->expenses = Expense::get();
   
    }

    public function updated($value){
        $this->validateOnly($value);
    }

    protected $messages =[
       
        'selectedExpense.*.required' => 'Expense field is required',
        'selectedExpense.0.required' => 'Expense field is required',
    ];
    protected $rules = [
        'selectedExpense.0' => 'required',
        'selectedExpense.*' => 'required',
    ];

    public function updatedSelectedExpense($id, $key = null)
    {
        if (!is_null($id)) {
            $expense = Expense::find($id);
            $lastTripExpense = TripExpense::where('expense_id', $id)->latest('id')->first();

            if ($key !== null) {
                // Make sure $this->amount is an array
                if (!is_array($this->amount)) {
                    $this->amount = [];
                }

                $this->amount[$key] = $expense->amount ?? null;
                $this->selectedCurrency[$key] = $expense->currency_id ?? null;
                $this->payment_method_id[$key] = $expense->payment_method_id ?? null;
                $this->category[$key] = $lastTripExpense->category ?? null;
                $this->selectedVendor[$key] = $lastTripExpense->vendor_id ?? null;
            } else {
                $this->amount = $expense->amount ?? null;
                $this->selectedCurrency = $expense->currency_id ?? null;
                $this->payment_method_id = $expense->payment_method_id ?? null;
                $this->category = $lastTripExpense->category ?? null;
                $this->selectedVendor = $lastTripExpense->vendor_id ?? null;
            }
        }
    }

       public function refresh($category){

        if($category == "expenses"){
            $this->expenses = Expense::orderBy('name','asc')->get();
             $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Expenses Refreshed Successfully!!."
            ]);
        } elseif($category == 'allowances'){
            $this->allowances = Allowance::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Allowances Refreshed Successfully!!."
            ]);
        } elseif($category == 'vendors'){
            $this->vendors = Vendor::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Vendors Refreshed Successfully!!."
            ]);
        }
    }

    public function updatedSelectedCurrency($id, $key = Null){
        if(!is_null($id)){
            if ($key) {
                $this->selected_currency[$key] = Currency::find($id);
            }else{
                $this->selected_currency = Currency::find($id);
            }
          
        }
    }

    public function updatedAmount($amount, $key = null){
        if (!is_null($amount)) {
            if (isset($key)) {
                if ((isset($this->exchange_rate[$key]) && $this->exchange_rate[$key] > 0)  &&  ( isset($amount) && $amount > 0 )) {
                    $this->exchange_amount[$key] = $this->exchange_rate[$key] * $amount;
                }
            }else{
                if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($amount) && $amount > 0 )) {
                    $this->exchange_amount = $this->exchange_rate * $amount;
                }
            }
        }
    }
    public function updatedExchangeRate($rate, $key = null){

        if (!is_null($rate)) {
            
            if (isset($key)) {
                if ((isset($this->amount[$key]) && $this->amount[$key] > 0)  &&  ( isset($rate) && $rate > 0 )) {
                    $this->exchange_amount[$key] = $this->amount[$key] * $rate;
                }
            }else{
                if ((isset($this->amount) && $this->amount > 0)  &&  ( isset($rate) && $rate > 0 )) {
                    $this->exchange_amount = $this->amount * $rate;
                }
            }
                
        }
        
    }

    public function store(){



            if ($this->trip_expense_type) {

                $created = false;

                foreach ($this->trip_expense_type as $key => $value) {
                    // Skip if type is missing or not recognized
                    if (!isset($this->trip_expense_type[$key])) {
                        continue;
                    }
                
                    $type = $this->trip_expense_type[$key];
                
                    // Skip if type is 'expense' but no expense selected
                    if ($type === 'expense' && empty($this->selectedExpense[$key])) {
                        continue;
                    }
                
                    // Skip if type is 'allowance' but no allowance selected
                    if ($type === 'allowance' && empty($this->selectedAllowance[$key])) {
                        continue;
                    }
                
                    // Proceed to save only valid records
                    $trip_expense = new TripExpense;
                    $trip_expense->user_id = Auth::user()->id;
                    $trip_expense->trip_id = $this->trip->id;
                    $trip_expense->currency_id = $this->selectedCurrency[$key] ?? null;
                    $trip_expense->vendor_id = $this->selectedVendor[$key] ?? null;
                    $trip_expense->payment_method_id = $this->payment_method_id[$key] ?? null;

                    if ($type === 'expense') {
                        $trip_expense->expense_id = $this->selectedExpense[$key];
                        $trip_expense->allowance_id = null;
                    } elseif ($type === 'allowance') {
                        $trip_expense->allowance_id = $this->selectedAllowance[$key];
                        $trip_expense->expense_id = null;

                        $allowance_driver = new AllowanceDriver;
                        $allowance_driver->driver_id = $this->trip->driver_id ? $this->trip->driver_id : Null;
                        $allowance_driver->trip_id = $this->trip->id ? $this->trip->id : Null;
                        $allowance_driver->allowance_id = $this->selectedAllowance[$key] ? $this->selectedAllowance[$key] : Null;    
                        $allowance_driver->currency_id = $this->selectedCurrency[$key] ?? null;     
                        $allowance_driver->amount = $this->amount[$key] ?? Null;      
                        $allowance_driver->save();

                    }
                
                    $trip_expense->category = $this->category[$key] ?? null;
                    $trip_expense->visible_on_trip_sheet = $this->visible_on_trip_sheet[$key] ?? true;
                    $trip_expense->amount = $this->amount[$key] ?? 0;
                    $trip_expense->exchange_rate = $this->exchange_rate[$key] ?? null;

                   if (isset($this->exchange_amount[$key]) && $this->exchange_amount[$key] !== null) {
                        $trip_expense->exchange_amount = $this->exchange_amount[$key];
                    } elseif (
                        isset($this->exchange_rate[$key], $this->amount[$key]) &&
                        $this->exchange_rate[$key] > 0 &&
                        $this->amount[$key] > 0
                    ) {
                        $trip_expense->exchange_amount = $this->exchange_rate[$key] * $this->amount[$key];
                    }
                                    
                   
                    $trip_expense->save();

                    app(TripExpenseJournalService::class)->postExpense($trip_expense->fresh());

                    $this->recalculateExpenses($this->trip->id);
                    $created = true;
                }


                $this->dispatchBrowserEvent('hide-tripExpenseModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Expense(s) Added Successfully!!"
                ]);

                if ($created) {
                    $this->syncNewExpensesToSage();
                }

                redirect(request()->header('Referer'));
    
                
            }
      
      
    }

    /**
     * Push newly-added (non-fuel) expense/allowance lines to Sage right away
     * instead of waiting for a manual trip resync — reuses the full trip sync
     * (ensures the project, then every vendor × currency group), which is
     * idempotent per group: a brand-new (vendor, currency) group gets its
     * Purchase Requisition / Dispatch Sheet created now; a group that already
     * has a document just reports "skipped" (there's no append-to-existing-
     * document capability yet, so a line added to an already-synced group
     * still needs a manual follow-up in Sage). Never blocks the expense save —
     * failures surface as a warning toast only, fuel-topup lines sync on their
     * own via FuelObserver so they're not touched here.
     */
    protected function syncNewExpensesToSage(): void
    {
        if (! $this->sageEnabled) {
            return;
        }

        try {
            $result = app(SageSyncService::class)->syncTrip($this->trip);

            if (empty($result['success']) && empty($result['skipped'])) {
                $this->dispatchBrowserEvent('alert', [
                    'type'    => 'warning',
                    'message' => 'Expense saved, but Sage sync failed: ' . ($result['error'] ?? 'unknown error'),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Sage trip-expense auto-sync failed: ' . $e->getMessage());
        }
    }

    private function recalculateExpenses($trip_id){

        $trip = Trip::find($trip_id);
        $this->trip_expenses = TripExpense::where('trip_id', $trip_id)->get();
        
        $this->total_transporter_expenses = 0;
        $this->total_customer_expenses = 0;
        $this->total_expenses = 0;
        
        if ($this->trip_expenses->isNotEmpty()) {
            foreach ($this->trip_expenses as $expense) {
                $use_amount = ($expense->currency_id == Auth::user()->employee->company->currency_id) 
                    ? $expense->amount 
                    : $expense->exchange_amount;
        
                if (is_numeric($use_amount)) {
                    switch ($expense->category) {
                        case 'Transporter':
                            $this->total_transporter_expenses += $use_amount;
                            break;
                        case 'Customer':
                            $this->total_customer_expenses += $use_amount;
                            break;
                        case 'Self':
                            $this->total_expenses += $use_amount;
                            break;
                    }
                }
            }
        }
        
        $this->cost_of_sales = $this->total_expenses;
        $trip->cost_of_sales = $this->cost_of_sales;
        $this->turnover = $trip->turnover;
        
        if ($this->cost_of_sales > 0 && $this->turnover > 0) {
            $this->net_profit = $this->turnover - $this->cost_of_sales;
            $trip->net_profit = $this->net_profit;
        
            if ($this->net_profit > 0) {
                $trip->markup_percentage = ($this->net_profit / $this->cost_of_sales) * 100;
                $trip->net_profit_percentage = ($this->net_profit / $this->turnover) * 100;
            } else {
                $trip->markup_percentage = 0;
                $trip->net_profit_percentage = 0;
            }
        } else {
            $trip->net_profit_percentage = 100;
            $trip->markup_percentage = 100;
        }
        
        $trip->update();

        $this->trip = Trip::find($trip->id);
    }

    public function edit($id){

        $expense = TripExpense::find($id);
        $this->user_id = $expense->user_id;
        $this->trip_id = $expense->trip_id;
        $this->edit = True;
        $this->trip = Trip::find($this->trip_id);
        $this->selectedCurrency = $expense->currency_id;
        $this->selectedVendor = $expense->vendor_id;
        $this->payment_method_id = $expense->payment_method_id;
        $this->selectedExpense = $expense->expense_id;
        $this->selectedAllowance = $expense->allowance_id;
        if ($expense->expense_id) {
            $this->trip_expense_type = "expense";
        }elseif($expense->allowance_id){
            $this->trip_expense_type = "allowance";
        }
        $this->category = $expense->category;
        $this->visible_on_trip_sheet = $expense->visible_on_trip_sheet;
        $this->amount = $expense->amount;
        $this->exchange_rate = $expense->exchange_rate;
        $this->exchange_amount = $expense->exchange_amount;
        $this->trip_expense_id = $expense->id;
        $this->dispatchBrowserEvent('show-tripExpenseEditModal');

        }

        public function update()
        {
            if ($this->trip_expense_id) {

                $trip_expense = TripExpense::find($this->trip_expense_id);
                $trip_expense->amount = $this->amount;
                $trip_expense->trip_id = $this->trip_id;
                $trip_expense->user_id = Auth::user()->id;
                if ($this->trip_expense_type == "expense") {
                    $trip_expense->expense_id = $this->selectedExpense;
                    $trip_expense->allowance_id = Null;
                }elseif($this->trip_expense_type == "allowance"){
                    $trip_expense->allowance_id = $this->selectedAllowance;
                    $trip_expense->expense_id = Null;
                }

                if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->amount) && $this->amount > 0 )) {

                    $this->exchange_amount = $this->exchange_rate * $this->amount;
        
                }

                $trip_expense->category = $this->category;
                $trip_expense->visible_on_trip_sheet = $this->visible_on_trip_sheet;
                $trip_expense->exchange_rate = $this->exchange_rate;
                $trip_expense->exchange_amount = $this->exchange_amount;
                $trip_expense->currency_id = $this->selectedCurrency;
                $trip_expense->vendor_id = $this->selectedVendor;
                $trip_expense->payment_method_id = $this->payment_method_id;
                $trip_expense->update();

                app(TripExpenseJournalService::class)->postExpense($trip_expense->fresh());

                $this->recalculateExpenses($this->trip->id);

                $this->dispatchBrowserEvent('hide-tripExpenseEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Expense Updated Successfully!!"
                ]);

                redirect(request()->header('Referer'));
           

            }
        }

        public function showDelete($id){
            $this->trip_expense_id = $id;
            $this->trip_expense = TripExpense::find($id);
            $this->dispatchBrowserEvent('show-expenseDeleteModal');
        }

        public function deleteExpense(){
            $bill = $this->trip_expense->bill;
            if (isset($bill)) {
                $bill_expenses = $bill->bill_expenses;
                if (isset($bill_expenses)) {
                    foreach ($bill_expenses as $bill_expense) {
                        $bill_expense->delete();
                    }
                }
                $bill->delete();
            }
            

           $this->trip_expense->delete();

           $this->recalculateExpenses($this->trip->id);

           $this->dispatchBrowserEvent('hide-expenseDeleteModal');
           $this->resetInputFields();
           $this->dispatchBrowserEvent('alert',[
               'type'=>'success',
               'message'=>"Expense Deleted Successfully!!"
           ]);
        }



    /** Sage sync column only shows when THIS trip's own company has Sage active. */
    public function getSageEnabledProperty()
    {
        return SageIntegration::activeForCompany($this->trip->company_id);
    }

    public function render()
    {

        $this->expenses = Expense::get();

        $this->trip_expenses = TripExpense::where('trip_id',$this->trip->id)->with(['vendor', 'currency'])->get();

        return view('livewire.trips.expenses', [
            'sync_badges' => $this->sageEnabled ? $this->syncBadges() : [],
        ]);
    }

    /**
     * Sage sync status per trip_expense row, keyed by trip_expense id.
     *
     * A fuel-topup line (fuel_id set) is its OWN "PR - Diesel" document,
     * mapped by entity_type="fuel_pr_diesel", local_id=fuel_id — it never
     * joins a requisition/dispatch group (SageRequisitionService excludes
     * fuel-linked lines to avoid double-posting the same spend twice).
     *
     * Every other expense/allowance line with a vendor: each (vendor,
     * currency) group on the trip shares ONE Purchase Requisition or
     * Dispatch Sheet (see SageRequisitionService::syncTripRequisitions), so
     * every line in a group shares the same badge/status.
     *
     * @return array<int, array{doc_type:string, status:?string, external_id:?string, error:?string, resync:string}>
     */
    protected function syncBadges(): array
    {
        $integration = $this->activeSageIntegration($this->trip->company_id);
        if (! $integration) {
            return [];
        }

        $badges = [];

        $fuelLines = $this->trip_expenses->filter(fn ($e) => $e->fuel_id);
        if ($fuelLines->isNotEmpty()) {
            $fuelMappings = IntegrationMapping::where('company_integration_id', $integration->id)
                ->where('entity_type', 'fuel_pr_diesel')
                ->whereIn('local_id', $fuelLines->pluck('fuel_id'))
                ->get()
                ->keyBy('local_id');

            foreach ($fuelLines as $e) {
                $mapping = $fuelMappings->get($e->fuel_id);

                $badges[$e->id] = [
                    'doc_type'    => 'PR - Diesel',
                    'status'      => $mapping->sync_status ?? null,
                    'external_id' => $mapping->external_id ?? null,
                    'error'       => $mapping->last_error ?? null,
                    'resync'      => 'resyncFuel',
                ];
            }
        }

        $groupLines = $this->trip_expenses->filter(fn ($e) => ! $e->fuel_id && ($e->expense_id || $e->allowance_id));
        if ($groupLines->isNotEmpty()) {
            $entityTypes = $groupLines
                ->map(fn ($e) => $this->requisitionEntityType($e))
                ->unique()
                ->values();

            $mappings = IntegrationMapping::where('company_integration_id', $integration->id)
                ->where('local_id', $this->trip->id)
                ->whereIn('entity_type', $entityTypes)
                ->get()
                ->keyBy('entity_type');

            foreach ($groupLines as $e) {
                $entity  = $this->requisitionEntityType($e);
                $mapping = $mappings->get($entity);

                $badges[$e->id] = [
                    'doc_type'    => SageRequisitionService::isDispatchVendor($e->vendor) ? 'Dispatch Sheet' : 'Purchase Requisition',
                    'status'      => $mapping->sync_status ?? null,
                    'external_id' => $mapping->external_id ?? null,
                    'error'       => $mapping->last_error ?? null,
                    'resync'      => 'resyncRequisition',
                ];
            }
        }

        return $badges;
    }

    /**
     * Push a single fuel-topup line's PR - Diesel to Sage (idempotent — an
     * already-synced-with-the-right-project fuel just reports "skipped").
     */
    public function resyncFuel($tripExpenseId)
    {
        if (! $this->sageEnabled) {
            return;
        }

        $fuel = optional(TripExpense::find($tripExpenseId))->fuel;
        if (! $fuel) {
            return;
        }

        if (strcasecmp((string) $fuel->authorization, 'approved') !== 0) {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'warning',
                'message' => 'Only authorized (approved) fuel orders can be synced to Sage.',
            ]);
            return;
        }

        $result = app(SageSyncService::class)->syncFuel($fuel);
        $ok     = ! empty($result['success']) && ! empty($result['external_id']);

        $this->dispatchBrowserEvent('alert', [
            'type'    => $ok ? 'success' : 'warning',
            'message' => $ok
                ? 'Fuel order synced to Sage PR - Diesel (' . $result['external_id'] . ').'
                : ('Sage sync: ' . ($result['error'] ?? 'see status.')),
        ]);
    }

    /**
     * Re-sync this trip's Purchase Requisitions / Dispatch Sheets to Sage —
     * reuses the full trip sync (ensures the project, then every vendor ×
     * currency group), idempotent per group so already-synced groups just
     * report "skipped".
     */
    public function resyncRequisition($tripExpenseId)
    {
        if (! $this->sageEnabled) {
            return;
        }

        $result = app(SageSyncService::class)->syncTrip($this->trip);

        $this->dispatchBrowserEvent('alert', [
            'type'    => ! empty($result['success']) ? 'success' : 'warning',
            'message' => ! empty($result['success'])
                ? 'Trip requisitions / dispatch sheets synced to Sage.'
                : ('Sage sync: ' . ($result['error'] ?? 'see status.')),
        ]);
    }

    /** Same (vendor × currency) mapping key SageRequisitionService::syncGroup() writes under. */
    protected function requisitionEntityType(TripExpense $e): string
    {
        $prefix = SageRequisitionService::isDispatchVendor($e->vendor) ? 'trip_dispatch_v' : 'trip_requisition_v';

        return $prefix . ($e->vendor_id ?: 0) . '_c' . ($e->currency_id ?: 0);
    }
}
