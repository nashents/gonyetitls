<?php

namespace App\Http\Livewire\quotations;

use App\Models\Trip;
use Livewire\Component;
use App\Models\Quotation;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\quotationTripExport;
use Illuminate\Support\Facades\Auth;
use App\Exports\QuotationTripsExport;

class Trips extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    private $trips;
    public $quotation;
    public $quotation_id;
    public $company;
    public $user;
    public $employee;
    public $role_names = [];
    public $department_names = [];
    public $rank_names = [];

    public function mount($id){
       $this->resetPage();
       $this->quotation_id = $id;
       $this->quotation = Quotation::find($id);
       $this->user = Auth::user();
        $this->employee = $this->user->employee;
        $this->company = $this->employee->company;
         foreach($this->employee->departments as $department) {
            $this->department_names[] = $department->name;
        }
    
        foreach($this->user->roles as $role) {
            $this->role_names[] = $role->name;
        }
    
        foreach($this->employee->ranks as $rank) {
            $this->rank_names[] = $rank->name;
        }

    }


    public function exportTripsCSV(Excel $excel){

        return $excel->download(new QuotationTripsExport($this->quotation_id), 'quotation_trips.csv', Excel::CSV);
    }
    public function exportTripsPDF(Excel $excel){

        return $excel->download(new QuotationTripsExport($this->quotation_id), 'quotation_trips.pdf', Excel::DOMPDF);
    }
    public function exportTripsExcel(Excel $excel){
        return $excel->download(new QuotationTripsExport($this->quotation_id), 'quotation_trips.xlsx');
    }

    public function render()
    {
        $this->trips = Trip::where('quotation_id',$this->quotation_id)->whereYear('start_date',date('Y'))->paginate(10);
        return view('livewire.quotations.trips',[
            'trips' => $this->trips
        ]);
    }
}
