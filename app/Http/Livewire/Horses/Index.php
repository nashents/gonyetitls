<?php

namespace App\Http\Livewire\Horses;

use App\Models\Bill;
use App\Models\Horse;
use App\Models\Mileage;
use Livewire\Component;
use App\Models\Currency;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\HorsesExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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

    public function exportHorsesCSV(Excel $excel){

        return $excel->download(new HorsesExport, 'horses.csv', Excel::CSV);
    }
    public function exportHorsesPDF(Excel $excel){

        return $excel->download(new HorsesExport, 'horses.pdf', Excel::DOMPDF);
    }
    public function exportHorsesExcel(Excel $excel){
        return $excel->download(new HorsesExport, 'horses.xlsx');
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
            $expenses = Null;
            $distance = Null;
            $bills = Bill::where('horse_id',$id)->where('authorization','approved')->whereYear('created_at',date('Y'))->get();

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

            $last_mileage = Mileage::where('horse_id',$id)->whereYear('created_at', date('Y'))->orderBy('created_at','desc')->first();
            $first_mileage = Mileage::where('horse_id',$id)->whereYear('created_at', date('Y'))->orderBy('created_at','asc')->first();
            
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
