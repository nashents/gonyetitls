<?php

namespace App\Http\Livewire\deals;


use App\Models\Cargo;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\UnitsOfMeasure;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];

    public $deal;
    public $deal_id;
    public $user_id;
    public $company_id;
    public $customer_id;
    public $cargo_id;
    public $units_of_measure_id;
    public $weight;
    public $litreage;
    public $quantity;
    public $start_date;
    public $end_date;
    public $deal_number;
    public $reference;
    public $cargo_type;
    public $rate;
    public $freight;
    public $currency_id;
    public $notes;
    public $status = 1;

    public $statusUpdateId;
    public $statusUpdateTarget;
    public $statusUpdateComment;
    public $statusUpdateWeight;
    public $statusUpdateLitreage;
    public $statusUpdateQuantity;
    public $statusUpdateCargoType;

    public $customers = [];
    public $cargos = [];
    public $currencies = [];
    public $units_of_measures = [];

    public function mount()
    {
        $this->cargo_type = "Solid"; 
        $this->company_id = Auth::user()->employee->company_id ?? null;

        $this->customers = Customer::orderBy('name', 'asc')->get();
        $this->currencies = Currency::orderBy('name', 'asc')->get();
        $this->cargos = Cargo::orderBy('name', 'asc')->get();
        $this->units_of_measures = UnitsOfMeasure::orderBy('name', 'asc')->get();
    }

    public function dealNumber(){

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

        $deal = Deal::latest()->orderBy('id','desc')->first();

        if (!$deal) {
            $deal_number =  $initials .'D'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $deal->id + 1;
            $deal_number =  $initials .'D'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $deal_number;


    }

    /** Opens the shared status-update modal for either the Completed or Cancelled action. */
    public function openStatusUpdate($id, $target){
        if(is_null($id)){
            return;
        }

        $deal = Deal::find($id);
        if(!$deal){
            return;
        }

        if($target === 'Cancelled' && $deal->trips()->where('trip_status', '!=', 'Cancelled')->exists()){
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'This deal has trips attached that are not Cancelled. Cancel those trips first.'
            ]);
            return;
        }

        $this->deal = $deal;
        $this->statusUpdateId = $id;
        $this->statusUpdateTarget = $target;
        $this->statusUpdateComment = null;
        $this->statusUpdateWeight = $deal->weight;
        $this->statusUpdateLitreage = $deal->litreage;
        $this->statusUpdateQuantity = $deal->quantity;
        $this->statusUpdateCargoType = Cargo::find($deal->cargo_id)?->type;

        $this->dispatchBrowserEvent('show-dealStatusModal');
    }

    public function saveStatusUpdate(){
        if(!$this->statusUpdateId){
            return;
        }

        $deal = Deal::find($this->statusUpdateId);
        if(!$deal){
            return;
        }

        if($this->statusUpdateTarget === 'Cancelled' && $deal->trips()->where('trip_status', '!=', 'Cancelled')->exists()){
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'This deal has trips attached that are not Cancelled. Cancel those trips first.'
            ]);
            return;
        }

        $this->validate([
            'statusUpdateComment' => $this->statusUpdateTarget === 'Cancelled' ? 'required|string' : 'nullable|string',
        ], [
            'statusUpdateComment.required' => 'Please provide a reason for cancelling this deal.',
        ]);

        $deal->status = False;
        $deal->is_closed = True;
        $deal->closed_at = now();
        $deal->status_comment = $this->statusUpdateComment ?: $deal->status_comment;

        if($this->statusUpdateTarget === 'Completed'){
            $deal->completed = True;
            $deal->cancelled = False;
            $deal->completed_by = Auth::id();
            $deal->completed_at = now();
            // Record what was ACTUALLY pushed/delivered without overwriting the
            // deal's targets, so the summary keeps both (target 4000t vs 2000t done).
            $deal->completed_weight = $this->statusUpdateWeight;
            $deal->completed_litreage = $this->statusUpdateLitreage;
            $deal->completed_quantity = $this->statusUpdateQuantity;
        }else{
            $deal->cancelled = True;
            $deal->completed = False;
            $deal->cancelled_by = Auth::id();
            $deal->cancelled_at = now();
        }

        $deal->save();

        $targetLabel = $this->statusUpdateTarget;

        $this->dispatchBrowserEvent('hide-dealStatusModal');
        $this->resetStatusUpdateFields();

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Deal marked as {$targetLabel}!!"
        ]);
    }

    private function resetStatusUpdateFields(){
        $this->deal = null;
        $this->statusUpdateId = null;
        $this->statusUpdateTarget = null;
        $this->statusUpdateComment = null;
        $this->statusUpdateWeight = null;
        $this->statusUpdateLitreage = null;
        $this->statusUpdateQuantity = null;
        $this->statusUpdateCargoType = null;
    }

    public function updated($value)
    {
        $this->validateOnly($value);
    }

    private function resetInputFields()
    {
        $this->deal_id = null;
        $this->customer_id = null;
        $this->currency_id = null;
        $this->rate = null;
        $this->freight = null;
        $this->cargo_id = null;
        $this->units_of_measure_id = null;
        $this->weight = null;
        $this->litreage = null;
        $this->quantity = null;
        $this->start_date = null;
        $this->end_date = null;
        $this->deal_number = null;
        $this->reference = null;
        $this->notes = null;
        $this->status = 1;
    }

    protected function rules()
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'cargo_id' => 'required|exists:cargos,id',
            'currency_id' => 'nullable|exists:cargos,id',
            'units_of_measure_id' => 'nullable|exists:units_of_measures,id',
            'rate' => 'nullable|numeric|min:0',
            'freight' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'litreage' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reference' => 'nullable|string|max:255',
            'status' => 'required|boolean',
        ];
    }

    public function updatedCargoId($id){
        if(is_null($id)){
            return;
        }

        $this->cargo_type = Cargo::find($id)?->type;
    }

    public function store()
    {
       
            $this->validate();

            $deal = new Deal;
            $deal->user_id = Auth::id();
            $deal->company_id = $this->company_id;
            $deal->customer_id = $this->customer_id;
            $deal->currency_id = $this->currency_id;
            $deal->cargo_id = $this->cargo_id;
            $deal->units_of_measure_id = $this->units_of_measure_id;
            $deal->weight = $this->weight;
            $deal->litreage = $this->litreage;
            $deal->quantity = $this->quantity;
            $deal->start_date = $this->start_date;
            $deal->end_date = $this->end_date;
            $deal->deal_number = $this->dealNumber();
            $deal->reference = $this->reference;
            $deal->rate = $this->rate;
            $deal->freight = $this->freight;
            $deal->notes = $this->notes;
            $deal->status = $this->status;
            $deal->save();

            $this->dispatchBrowserEvent('hide-dealModal');
            $this->resetInputFields();

            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => 'Deal Created Successfully!!'
            ]);
     
    }

    public function edit($id)
    {
        $deal = Deal::find($id);

        if (!$deal) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Deal not found!!'
            ]);
            return;
        }
        $this->cargo_type = Cargo::find($deal->cargo_id)?->type;
        $this->deal_id = $deal->id;
        $this->user_id = $deal->user_id;
        $this->company_id = $deal->company_id;
        $this->currency_id = $deal->currency_id;
        $this->rate = $deal->rate;
        $this->freight = $deal->freight;
        $this->customer_id = $deal->customer_id;
        $this->cargo_id = $deal->cargo_id;
        $this->units_of_measure_id = $deal->units_of_measure_id;
        $this->weight = $deal->weight;
        $this->litreage = $deal->litreage;
        $this->quantity = $deal->quantity;
        $this->start_date = $deal->start_date ? date('Y-m-d\TH:i', strtotime($deal->start_date)) : null;
        $this->end_date = $deal->end_date ? date('Y-m-d\TH:i', strtotime($deal->end_date)) : null;
        $this->deal_number = $deal->deal_number;
        $this->reference = $deal->reference;
        $this->notes = $deal->notes;
        $this->status = $deal->status;

        $this->dispatchBrowserEvent('show-dealEditModal');
    }

    public function update()
    {
        if ($this->deal_id) {
         
                $this->validate();

                $deal = Deal::find($this->deal_id);

                if (!$deal) {
                    $this->dispatchBrowserEvent('alert', [
                        'type' => 'error',
                        'message' => 'Deal not found!!'
                    ]);
                    return;
                }

                $deal->company_id = $this->company_id;
                $deal->customer_id = $this->customer_id;
                $deal->cargo_id = $this->cargo_id;
                $deal->units_of_measure_id = $this->units_of_measure_id;
                $deal->weight = $this->weight;
                $deal->currency_id = $this->currency_id;
                $deal->litreage = $this->litreage;
                $deal->quantity = $this->quantity;
                $deal->rate = $this->rate;
                $deal->freight = $this->freight;
                $deal->start_date = $this->start_date;
                $deal->end_date = $this->end_date;
                $deal->deal_number = $this->deal_number;
                $deal->reference = $this->reference;
                $deal->status = $this->status;
                $deal->notes = $this->notes;
                $deal->update();

                $this->dispatchBrowserEvent('hide-dealEditModal');
                $this->resetInputFields();

                $this->dispatchBrowserEvent('alert', [
                    'type' => 'success',
                    'message' => 'Deal Updated Successfully!!'
                ]);
          
        }
    }

    public function delete($id){
        $this->deal_id  = $id;
        $this->dispatchBrowserEvent('show-deleteModal');
    }

    public function destroy(){

        $deal = Deal::find($this->deal_id);
        $deal->destroy();
        $this->dispatchBrowserEvent('hide-deleteModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Deal Deleted Successfully!!'
        ]);
    }

    public function render()
    {
        $search = trim($this->search);

        $query = Deal::query()
            ->with(['customer', 'cargo', 'units_of_measure'])
            ->withSum('trips', 'weight')
            ->withSum('trips', 'litreage')
            ->withSum('trips', 'quantity')
            ->when($this->company_id, function ($q) {
                $q->where('company_id', $this->company_id);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('deal_number', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhere('rate', 'like', "%{$search}%")
                        ->orWhere('freight', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('currency', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('cargo', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.deals.index', [
            'deals' => $query
        ]);
    }
}