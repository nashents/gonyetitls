<?php

namespace App\Http\Livewire\Rates;

use App\Exports\RatesExport;
use App\Models\Cargo;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Destination;
use App\Models\LoadingPoint;
use App\Models\OffloadingPoint;
use App\Models\Rate;
use App\Models\Transporter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    protected $rates;
    public $destinations;
    public $destination_id;
    public $from;
    public $to;
    public $offloading_points;
    public $offloading_point_id;
    public $loading_points;
    public $loading_point_id;
    public $currencies;
    public $currency_id;
    public $rate;
    public $weight;
    public $distance;
    public $freight_calculation;
    public $cargos;
    public $selectedCargo;
    public $cargo_type;
    public $litreage;
    public $customer_id;
    public $customers;
    public $transporter_id;
    public $transporters;
    public $category = "Customer";
    



    public $rate_id;
    public $user_id;

    public function mount(){
        $this->freight_calculation = "flat_rate";
        $this->cargo_type = "Solid";
        $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->cargos = Cargo::orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->loading_points = LoadingPoint::orderBy('name','asc')->get();
        $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
    }
    private function resetInputFields(){
        $this->from = '';
        $this->to = '';
        $this->rate = '';
        $this->weight = '';
        $this->category = '';
        $this->selectedCargo = '';
        $this->freight_calculation = '';
        $this->distance = '';
        $this->litreage = '';
        $this->currency_id = '';
        $this->offloading_point_id = '';
        $this->loading_point_id = '';
        $this->customer_id = '';
        $this->transporter_id = '';
    }

    public function exportratesCSV(Excel $excel){

        return $excel->download(new RatesExport, 'rates_'.time().'.csv', Excel::CSV);
    }
    public function exportratesPDF(Excel $excel){

        return $excel->download(new RatesExport, 'rates_'.time().'.pdf', Excel::DOMPDF);
    }
    public function exportratesExcel(Excel $excel){

        return $excel->download(new RatesExport, 'rates_'.time().'.xlsx');
    }
    public function updated($value){
        $this->validateOnly($value);
    }

    public function refresh($category){

        if($category == 'transporters'){
            $this->transporters = Transporter::where('authorization','approved')->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Transporters Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'customers'){
            $this->customers = Customer::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Customers Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'cargos'){
            $this->cargos = Cargo::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Cargos Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'currencies'){
            $this->currencies = Currency::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Currencies Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'loading_points'){
            $this->loading_points = LoadingPoint::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Loading Points Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'offloading_points'){
            $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Offloading Points Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'destinations'){
            $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Destinations Refreshed Successfully!!."
            ]);
        }

    }

    protected $rules = [
        'category' => 'required',
        'from' => 'required',
        'to' => 'required',
        'rate' => 'required',
        'currency_id' => 'required',
    ];

    public function updatedSelectedCargo($id)
    {
            if (!is_null($id)) {
                $cargo = Cargo::find($id);
                $this->cargo_type = $cargo->type;
            }
    }

    public function store(){
        
        DB::transaction(function () {

            $this->validate();

            $rate = new Rate;
            $rate->user_id = Auth::user()->id;
            $rate->from = $this->from;
            $rate->to = $this->to;
            $rate->customer_id = $this->customer_id;
            $rate->transporter_id = $this->transporter_id;
            $rate->loading_point_id = $this->loading_point_id;
            $rate->offloading_point_id = $this->offloading_point_id;
            $rate->currency_id = $this->currency_id;
            $rate->cargo_id = $this->selectedCargo;
            $rate->rate = $this->rate;
            $rate->distance = $this->distance;
            $rate->freight_calculation = $this->freight_calculation;
            $rate->category = $this->category;
            $rate->weight = $this->weight;
            $rate->litreage = $this->litreage;
            $rate->save();

            $this->dispatchBrowserEvent('hide-rateModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Rate Created Successfully!!"
            ]);

        });

    }

    public function edit($id){
    $rate = Rate::find($id);
    $this->from = $rate->from;
    $this->to = $rate->to;
    $this->loading_point_id = $rate->loading_point_id;
    $this->offloading_point_id = $rate->offloading_point_id;
    $this->currency_id = $rate->currency_id;
    $this->freight_calculation = $rate->freight_calculation;
    $this->distance = $rate->distance;
    $this->selectedCargo = $rate->cargo_id;
    $this->customer_id = $rate->customer_id;
    $this->transporter_id = $rate->transporter_id;
    $this->rate = $rate->rate;
    $this->weight = $rate->weight;
    $this->litreage = $rate->litreage;
    $this->category = $rate->category;
    $this->rate_id = $rate->id;
    $this->dispatchBrowserEvent('show-rateEditModal');

    }



    public function update()
    {
        DB::transaction(function () {
            if ($this->rate_id) {
            
                $rate = Rate::find($this->rate_id);
                $rate->from = $this->from;
                $rate->to = $this->to;
                $rate->loading_point_id = $this->loading_point_id;
                $rate->offloading_point_id = $this->offloading_point_id;
                $rate->customer_id = $this->customer_id;
                $rate->transporter_id = $this->transporter_id;
                $rate->currency_id = $this->currency_id;
                $rate->cargo_id = $this->selectedCargo;
                $rate->freight_calculation = $this->freight_calculation;
                $rate->distance = $this->distance;
                $rate->rate = $this->rate;
                $rate->weight = $this->weight;
                $rate->category = $this->category;
                $rate->litreage = $this->litreage;
                $rate->update();
                $this->dispatchBrowserEvent('hide-rateEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Rate Updated Successfully!!"
                ]);

            }
        });
    }

    public function delete($id){
        $this->rate_id = $id;
        $this->dispatchBrowserEvent('show-deleteModal');
    }
    public function destroy(){
        $rate = Rate::find($this->rate_id);
        $rate->delete();
        $this->dispatchBrowserEvent('hide-deleteModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Rate Deleted Successfully!!"
        ]);
    }

    public function render()
    {

        $rates = Rate::query()
        ->with([
            'customer',
            'transporter',
            'cargo',
            'loading_point',
            'offloading_point',
            'currency',
        ])
        ->when($this->search, function ($query) {
            $search = trim($this->search);

            $query->where(function ($q) use ($search) {
                $q->where('from', 'like', '%' . $search . '%')
                  ->orWhere('to', 'like', '%' . $search . '%')
                  ->orWhere('rate', 'like', '%' . $search . '%')
                  ->orWhereHas('customer', function ($sub) use ($search) {
                      $sub->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('transporter', function ($sub) use ($search) {
                      $sub->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('cargo', function ($sub) use ($search) {
                      $sub->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('loading_point', function ($sub) use ($search) {
                      $sub->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('offloading_point', function ($sub) use ($search) {
                      $sub->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('destination', function ($sub) use ($search) {
                      $sub->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('currency', function ($sub) use ($search) {
                      $sub->where('name', 'like', '%' . $search . '%')
                          ->orWhere('symbol', 'like', '%' . $search . '%');
                  });
            });
        })
        ->latest()
        ->paginate(10);
      
        return view('livewire.rates.index',[
            'rates' => $rates,
           
        ]);
    }
}
