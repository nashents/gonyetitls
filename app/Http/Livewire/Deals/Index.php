<?php

namespace App\Http\Livewire\deals;


use App\Models\Deal;
use App\Models\Customer;
use App\Models\Cargo;
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
    public $status = 1;

    public $customers = [];
    public $cargos = [];
    public $units_of_measures = [];

    public function mount()
    {
        $this->company_id = Auth::user()->employee->company_id ?? Auth::user()->company_id ?? null;

        $this->customers = Customer::orderBy('name', 'asc')->get();
        $this->cargos = Cargo::orderBy('name', 'asc')->get();
        $this->units_of_measures = UnitsOfMeasure::orderBy('name', 'asc')->get();
    }



    public function updated($value)
    {
        $this->validateOnly($value);
    }

    private function resetInputFields()
    {
        $this->deal_id = null;
        $this->customer_id = null;
        $this->cargo_id = null;
        $this->units_of_measure_id = null;
        $this->weight = null;
        $this->litreage = null;
        $this->quantity = null;
        $this->start_date = null;
        $this->end_date = null;
        $this->deal_number = null;
        $this->reference = null;
        $this->status = 1;
    }

    protected function rules()
    {
        return [
            'customer_id' => 'nullable|exists:customers,id',
            'cargo_id' => 'nullable|exists:cargos,id',
            'units_of_measure_id' => 'nullable|exists:units_of_measures,id',
            'weight' => 'nullable|numeric|min:0',
            'litreage' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'deal_number' => 'required|string|min:2|unique:deals,deal_number,' . $this->deal_id . ',id,deleted_at,NULL',
            'reference' => 'nullable|string|max:255',
            'status' => 'required|boolean',
        ];
    }

    public function store()
    {
        try {
            $this->validate();

            $deal = new Deal;
            $deal->user_id = Auth::id();
            $deal->company_id = $this->company_id;
            $deal->customer_id = $this->customer_id;
            $deal->cargo_id = $this->cargo_id;
            $deal->units_of_measure_id = $this->units_of_measure_id;
            $deal->weight = $this->weight;
            $deal->litreage = $this->litreage;
            $deal->quantity = $this->quantity;
            $deal->start_date = $this->start_date;
            $deal->end_date = $this->end_date;
            $deal->deal_number = $this->deal_number;
            $deal->reference = $this->reference;
            $deal->status = $this->status;
            $deal->save();

            $this->dispatchBrowserEvent('hide-dealModal');
            $this->resetInputFields();

            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => 'Deal Created Successfully!!'
            ]);
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Something went wrong while creating deal!!'
            ]);
        }
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

        $this->deal_id = $deal->id;
        $this->user_id = $deal->user_id;
        $this->company_id = $deal->company_id;
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
        $this->status = $deal->status;

        $this->dispatchBrowserEvent('show-dealEditModal');
    }

    public function update()
    {
        if ($this->deal_id) {
            try {
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
                $deal->litreage = $this->litreage;
                $deal->quantity = $this->quantity;
                $deal->start_date = $this->start_date;
                $deal->end_date = $this->end_date;
                $deal->deal_number = $this->deal_number;
                $deal->reference = $this->reference;
                $deal->status = $this->status;
                $deal->update();

                $this->dispatchBrowserEvent('hide-dealEditModal');
                $this->resetInputFields();

                $this->dispatchBrowserEvent('alert', [
                    'type' => 'success',
                    'message' => 'Deal Updated Successfully!!'
                ]);
            } catch (\Exception $e) {
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'error',
                    'message' => 'Something went wrong while updating deal!!'
                ]);
            }
        }
    }

    public function render()
    {
        $search = trim($this->search);

        $query = Deal::query()
            ->with(['customer', 'cargo', 'units_of_measure'])
            ->when($this->company_id, function ($q) {
                $q->where('company_id', $this->company_id);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('deal_number', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($query) use ($search) {
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