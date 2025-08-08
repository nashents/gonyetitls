<?php

namespace App\Http\Livewire\Shifts;

use Carbon\Carbon;
use App\Models\Cargo;
use App\Models\Horse;
use App\Models\Shift;
use App\Models\Driver;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Transporter;
use App\Models\LoadingPoint;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use Livewire\WithFileUploads;
use App\Models\OffloadingPoint;
use App\Exports\ShiftsReportExport;

class Reports extends Component
{
    use WithPagination;
    use WithFileUploads;


    protected $paginationTheme = 'bootstrap';
    public $search;
    public $shift_filter;
    // protected $queryString = ['selectedDriver', 'selectedHorse','selectedTransporter',
    //                           'selectedVehicle','selectedCargo','selectedEmployee','selectedLoadingPoint','selectedOffloadingPoint',
    //                         ];
    public $from;
    public $to;
    private $shifts;
    public $shift_id;
    public $loading_points;
    public $selectedLoadingPoint;
    public $offloading_points;
    public $selectedOffloadingPoint;
    public $transporters;
    public $selectedTransporter;
    public $horses;
    public $selectedHorse;
    public $vehicles;
    public $selectedVehicle;
    public $drivers;
    public $selectedDriver;
    public $employees;
    public $selectedEmployee;
    public $cargos;
    public $selectedCargo;
    public $customers;
    public $selectedCustomer;
    public $type;
    public $filters;
  

    public function mount(){

        $this->shift_filter = "created_at";
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->cargos = Cargo::orderBy('name','asc')->get();
        $this->loading_points = LoadingPoint::orderBy('name','asc')->get();
        $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
        $this->horses = Horse::orderBy('registration_number','asc')->get();
        $this->vehicle = Vehicle::orderBy('registration_number','asc')->get();
        $this->drivers = Driver::with('employee')->get()->sortBy('driver.employee.name');
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();

      
    }

    public function previewReports(){

        return redirect()->route('shifts.preview', [
            'shift_filter' => $this->shift_filter,
            'selectedTransporter' => $this->selectedTransporter,
            'selectedCustomer' => $this->selectedCustomer,
            'driver' => $this->selectedDriver,
            'selectedEmployee' => $this->selectedEmployee,
            'selectedHorse' => $this->selectedHorse,
            'from' => $this->from,
            'to' => $this->to,
            'selectedCargo' => $this->selectedCargo,
            'selectedVehicle' => $this->selectedVehicle,
            'type' => $this->type,
            'selectedLoadingPoint' => $this->selectedLoadingPoint,
            'selectedOffloadingPoint' => $this->selectedOffloadingPoint,
      
        ]);

    }

    public function exportShiftsReportCSV(Excel $excel){
        return $excel->download(new ShiftsReportExport($this->filters), 'shifts_' .time().'.csv', Excel::CSV);
    }
    public function exportShiftsReportPDF(Excel $excel){
        return $excel->download(new ShiftsReportExport($this->filters), 'shifts_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportShiftsReportExcel(Excel $excel){
        return $excel->download(new ShiftsReportExport($this->filters), 'shifts_' .time().'.xlsx');
    }

    public function updatedTransporter($id){
        if (!is_null($id)) {
            $this->horses = Horse::query()->where('transporter_id',$id)->get();
            $this->drivers = Driver::query()->where('transporter_id', $id)->get();   
        }
    }

       public function calculatedShiftDuration($shift){

        $start = Carbon::parse($shift->shift_start_time);
        $end = Carbon::parse($shift->shift_end_time);

        // If you have dates for the shift times, parse them directly
        // Otherwise, handle cases where only the time is given

        // If only time is stored and end is "before" start, assume it's the next day
        if ($end->lessThan($start)) {
            $end->addDay();
        }

        // Get total seconds difference (works for > 24 hours too)
        $diffInSeconds = $end->diffInSeconds($start);

        // Convert to hours, minutes, and seconds
        $hours = floor($diffInSeconds / 3600);
        $minutes = floor(($diffInSeconds % 3600) / 60);
        $seconds = $diffInSeconds % 60;

        // Format as HH:MM:SS, even if hours > 24
        $durationFormatted = sprintf('%02dH: %02dM: %02dS', $hours, $minutes, $seconds);
        return $durationFormatted;
    }

    public function render()
    {

          $this->filters = [
            'shift_filter' => $this->shift_filter,
            'selectedTransporter' => $this->selectedTransporter,
            'selectedCustomer' => $this->selectedCustomer,
            'selectedDriver' => $this->selectedDriver,
            'selectedEmployee' => $this->selectedEmployee,
            'selectedHorse' => $this->selectedHorse,
            'from' => $this->from,
            'to' => $this->to,
            'selectedCargo' => $this->selectedCargo,
            'selectedVehicle' => $this->selectedVehicle,
            'type' => $this->type,
            'selectedLoadingPoint' => $this->selectedLoadingPoint,
            'selectedOffloadingPoint' => $this->selectedOffloadingPoint,
        ];
       
         $query = Shift::query();

        // Filter by Loading Point
       
        // Filter by Driver
        if (!empty($this->selectedDriver)) {
            $query->where('driver_id', $this->selectedDriver);
        }

        // Filter by Horse
        if (!empty($this->selectedHorse)) {
            $query->where('horse_id', $this->selectedHorse);
        }

        // Filter by Customer
        if (!empty($this->selectedCustomer)) {
            $query->where('customer_id', $this->selectedCustomer);
        }

        // Filter by Date Range
        if (!empty($this->from) && !empty($this->to)) {
            $query->whereBetween($this->shift_filter, [$this->from, $this->to]);
        }else {
           $query->whereMonth($this->shift_filter, date('m'))
           ->whereYear($this->shift_filter, date('Y'));
        }

        if (!empty($this->selectedLoadingPoint)) {
            $query->whereHas('loading_points', function ($q) {
                $q->where('loading_points.id', $this->selectedLoadingPoint);
            });
        }

         if (!empty($this->selectedOffloadingPoint)) {
            $query->whereHas('offloading_points', function ($q) {
                $q->where('offloading_points.id', $this->selectedOffloadingPoint);
            });
        }

        // Filter by Shift Time
        if (!empty($this->type)) {
            $query->where('type', $this->type);
        }

        // Final Query Execution
        return view('livewire.shifts.reports', [
            'shifts' => $query
                ->orderBy($this->shift_filter, 'desc')
                ->paginate(10),
        ]);
 
   
    }
}
