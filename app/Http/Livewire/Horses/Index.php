<?php

namespace App\Http\Livewire\Horses;

use App\Exports\HorsesExport;
use App\Models\Bill;
use App\Models\Currency;
use App\Models\Horse;
use App\Models\Mileage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    
    private $horses;
    public $currencies;
    public $revenue;
    public $currency_id;
    public $horse_id;
    public $from;
    public $to;

    public function exportHorsesCSV(Excel $excel){

        return $excel->download(new HorsesExport($this->from, $this->to, $this->search), 'horses_'.time().'.csv', Excel::CSV);
    }
    public function exportHorsesPDF(Excel $excel){

        return $excel->download(new HorsesExport($this->from, $this->to, $this->search), 'horses_'.time().'.pdf', Excel::DOMPDF);
    }
    public function exportHorsesExcel(Excel $excel){
        return $excel->download(new HorsesExport($this->from, $this->to, $this->search), 'horses_'.time().'.xlsx');
    }

    public function mount(){
        $this->from = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->to   = Carbon::now()->format('Y-m-d');
        $this->resetPage();
        $this->currencies = Currency::all();
      }

      public function updatingSearch()
      {
          $this->resetPage();
      }

    public function deactivate($id){
        $horse = Horse::find($id);
        $horse->status = 0 ;
        $horse->update();
        Session::flash('success','Horse successfully deactivated');
        return redirect(route('horses.index'));
    }

    public function activate($id){
        $horse = Horse::find($id);
        $horse->status = 1 ;
        $horse->update();
        Session::flash('success','Horse successfully deactivated');
        return redirect(route('horses.index'));
    }

    public function calculateCPK($id){

            $cpk = Null;
            $distance = Null;
            $bills = Bill::where('horse_id',$id)->where('authorization','approved')->whereBetween('created_at', [$this->from, $this->to])->get();

            $expenses = 0.0;

            if (!empty($bills)) {
                foreach ($bills as $bill) {
                    $amount = ($bill->currency_id == Auth::user()->employee->company->currency_id) 
                        ? $bill->total 
                        : $bill->exchange_amount;

                    $expenses += (float) $amount;
                }
            } else {
                $expenses = null;
            }

            $last_mileage = Mileage::where('horse_id',$id)->whereBetween('created_at', [$this->from, $this->to])->orderBy('created_at','desc')->first();
            $first_mileage = Mileage::where('horse_id',$id)->whereBetween('created_at', [$this->from, $this->to])->orderBy('created_at','asc')->first();
            
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
            return view('livewire.horses.index',[
                'horses' => Horse::with('transporter:id,name','horse_make:id,name','horse_model:id,name')
                ->where('archive',0)
                ->where('horse_number','like', '%'.$this->search.'%')
                ->orWhere('registration_number','like', '%'.$this->search.'%')
                ->orWhere('fleet_number','like', '%'.$this->search.'%')
                ->orWhereHas('horse_make', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('horse_model', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('transporter', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('registration_number','asc')->paginate(10)
            ]);
        }else{
            return view('livewire.horses.index',[
                'horses' => Horse::with('transporter:id,name','horse_make:id,name','horse_model:id,name')
                ->where('archive',0)->orderBy('registration_number','asc')->paginate(10)
            ]);
        }
       
    }
}
