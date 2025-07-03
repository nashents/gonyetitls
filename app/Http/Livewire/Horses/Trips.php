<?php

namespace App\Http\Livewire\Horses;

use App\Models\Trip;
use App\Models\Horse;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\HorseTripExport;
use Illuminate\Support\Facades\Auth;

class Trips extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    private $trips;
    public $horse;
    public $horse_id;
    public $company;
    public $user;
    public $employee;
    public $role_names = [];
    public $department_names = [];
    public $rank_names = [];

    public function mount($id){
        $this->resetPage();
        $this->horse_id = $id;
        $this->horse = Horse::find($id);
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

        return $excel->download(new HorseTripExport($this->horse_id), 'horse_trips.csv', Excel::CSV);
    }
    public function exportTripsPDF(Excel $excel){

        return $excel->download(new HorseTripExport($this->horse_id), 'horse_trips.pdf', Excel::DOMPDF);
    }
    public function exportTripsExcel(Excel $excel){
        return $excel->download(new HorseTripExport($this->horse_id), 'horse_trips.xlsx');
    }

    public function render()
    {
        $this->trips = Trip::where('horse_id',$this->horse_id)->whereYear('start_date',date('Y'))->paginate(10);
        return view('livewire.horses.trips',[
            'trips' => $this->trips
        ]);
    }
}
