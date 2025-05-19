<?php

namespace App\Http\Livewire\Vehicles;

use App\Models\Bill;
use App\Models\Mileage;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Currency;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\VehiclesExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $currencies;
    public $revenue;
    public $currency_id;
    private $vehicles;

    public function exportVehiclesCSV(Excel $excel){

        return $excel->download(new VehiclesExport, 'vehicles.csv', Excel::CSV);
    }
    public function exportVehiclesPDF(Excel $excel){

        return $excel->download(new VehiclesExport, 'vehicles.pdf', Excel::DOMPDF);
    }
    public function exportVehiclesExcel(Excel $excel){
        return $excel->download(new VehiclesExport, 'vehicles.xlsx');
    }
  

    public function mount(){
        $this->resetPage();
          $this->currencies = Currency::all();
      }

      public function updatingSearch()
      {
          $this->resetPage();
      }

    public function deactivate($id){
        $vehicle = Vehicle::find($id);
        $vehicle->status = 0 ;
        $vehicle->update();
        Session::flash('success','Vehicle successfully deactivated');
        return redirect(route('vehicles.index'));
    }

    public function activate($id){
        $vehicle = Vehicle::find($id);
        $vehicle->status = 1 ;
        $vehicle->update();
        Session::flash('success','Vehicle successfully deactivated');
        return redirect(route('vehicles.index'));
    }

     public function calculateCPK($id){

            $cpk = Null;
            $expenses = Null;
            $distance = Null;
            $bills = Bill::where('vehicle_id',$id)->where('authorization','approved')->whereYear('created_at',date('Y'))->get();

            if (isset($bills)) {
                foreach ($bills as $bill) {
                    if ($bill->currency_id == Auth::user()->employee->company->currency_id) {
                        $expenses = $expenses + $bill->total;
                    }elseif($bill->currency_id != Auth::user()->employee->company->currency_id){
                        $expenses = $expenses + $bill->exchange_amount;
                    }else{
                        $expenses = Null;
                    }
                   
                }
            }else{
                $expenses = Null;
            }

            $last_mileage = Mileage::where('vehicle_id',$id)->whereYear('created_at', date('Y'))->orderBy('created_at','desc')->first();
            $first_mileage = Mileage::where('vehicle_id',$id)->whereYear('created_at', date('Y'))->orderBy('created_at','asc')->first();
            
            if ((isset($last_mileage) && is_numeric($last_mileage)) && (isset($first_mileage) && is_numeric($first_mileage))) {

                if ($last_mileage > $first_mileage) {
                    $distance = $last_mileage - $first_mileage;
                }else{
                    $distance = Null;
                }

               
            }else {
                $distance = Null;
            }
           
            if ((isset($expenses) && is_numeric($expenses)) && (isset($distance) && is_numeric($distance)  )  ) {
                $cpk = $expenses / $distance;
                return $cpk;
            }else{
                return $cpk;
            }
          

           

    }

    public function render()
    {
        if (isset($this->search)) {
            return view('livewire.vehicles.index',[
                'vehicles' => Vehicle::with('transporter:id,name','vehicle_make:id,name','vehicle_model:id,name')
                ->where('archive',0)
                ->where('vehicle_number','like', '%'.$this->search.'%')
                ->orWhere('registration_number','like', '%'.$this->search.'%')
                ->orWhere('fleet_number','like', '%'.$this->search.'%')
                ->orWhereHas('vehicle_make', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('vehicle_model', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('transporter', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('registration_number','asc')->paginate(10)
            ]);
        }else{
            return view('livewire.vehicles.index',[
                'vehicles' => Vehicle::with('transporter:id,name','vehicle_make:id,name','vehicle_model:id,name')
                ->where('archive',0)->orderBy('registration_number','asc')->paginate(10)
            ]);
        }
    }
}
