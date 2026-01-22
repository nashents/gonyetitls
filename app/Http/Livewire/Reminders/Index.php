<?php

namespace App\Http\Livewire\Reminders;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Horse;
use App\Models\Fitness;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Reminder;
use App\Models\ReminderItem;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\RemindersExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
  
    private $reminders;
    public $type;
    public $name;
    public $reminder_items;
    public $reminder_item_id;
    public $fitnesses;
    public $fitness_id;
    public $issued_at;
    public $number;
    public $expires_at;
    public $reminder_at;
    public $first_reminder_at;
    public $first_reminder_at_status;
    public $second_reminder_at;
    public $second_reminder_at_status;
    public $third_reminder_at;
    public $third_reminder_at_status;
    public $horses;
    public $selectedHorse;
    public $trailers;
    public $selectedTrailer;
    public $company_id;
    public $vehicles;
    public $selectedVehicle;
    public $employees;
    public $selectedEmployee;
    public $user_id;
    public $status;
    public $reminder_copies;

    public $searchHorse;
    public $searchVehicle;
    public $searchTrailer;
    public $searchEmployee;
    public $cc = false;
    
    protected $queryString = ['search','searchVehicle','searchHorse','searchTrailer','searchEmployee'];

    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    private function resetInputFields(){
        $this->reminder_item_id = "" ;
        $this->issued_at = "";
        $this->expires_at = "" ;
        $this->cc = false;
        $this->inputs = [];
    }

    public function exportRemindersCSV(Excel $excel){
        return $excel->download(new RemindersExport, 'reminders_' .time().'.csv', Excel::CSV);
    }
    public function exportRemindersPDF(Excel $excel){
        return $excel->download(new RemindersExport, 'reminders_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportRemindersExcel(Excel $excel){
        return $excel->download(new RemindersExport, 'reminders_' .time().'.xlsx');
    }

    public function showCopies($id){
        $user = User::find($id);
        $this->reminder_copies = $user->reminder_copies;
    }

    public function mount(){
        $this->resetPage();
        $this->reminder_items = ReminderItem::orderBy('name','asc')->get();

        $this->horses = Horse::orderBy('registration_number','asc')->where('status',1)->latest()->get();

        $this->employees = Employee::orderBy('name','asc')->where('status',1)->get();

        $this->vehicles = Vehicle::orderBy('registration_number','asc')->where('status',1)->latest()->get();

        $this->trailers = Trailer::orderBy('registration_number','asc')->where('status',1)->latest()->get();
    }

    public function store(){
        // try{
          
            $fitness = new Fitness;
            $fitness->user_id = Auth::user()->id;
            $fitness->company_id = Auth::user()->employee ? Auth::user()->employee->company_id : Null;
            $fitness->horse_id = $this->selectedHorse ? $this->selectedHorse : Null;
            $fitness->trailer_id = $this->selectedTrailer ? $this->selectedTrailer : Null;
            $fitness->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : Null;
            $fitness->employee_id = $this->selectedEmployee ? $this->selectedEmployee : Null;
            $fitness->reminder_item_id = $this->reminder_item_id ? $this->reminder_item_id : Null;
            $fitness->type = $this->type ;
            $fitness->cc = $this->cc ;
    
            $fitness->issued_at = Carbon::create($this->issued_at)->toDateTimeString();
            $fitness->expires_at = Carbon::create($this->expires_at)->toDateTimeString();

            $fitness->first_reminder_at = Carbon::parse( $this->expires_at)->subDays(14);
            $fitness->first_reminder_at_status = 0;

            $fitness->second_reminder_at = Carbon::parse( $this->expires_at)->subDays(7);
            $fitness->second_reminder_at_status = 0;

            $fitness->third_reminder_at = Carbon::parse( $this->expires_at)->subDay();
            $fitness->third_reminder_at_status = 0;
          
            $today = now()->toDateTimeString();
            $expire = Carbon::create($this->expires_at)->toDateTimeString();
            if ($today <=  $expire) {
                $fitness->status = 1;
            }else{
                $fitness->status = 0;
            }
            $fitness->save();
            $this->dispatchBrowserEvent('hide-fitnessModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Reminder Set Successfully!!"
            ]);
    
        // }catch(\Exception $e){
        //     // Set Flash Message
        //     $this->dispatchBrowserEvent('alert',[
        //         'type'=>'error',
        //         'message'=>"Something goes wrong while creating fitness!!"
        //     ]);
        // }
    
        }

        public function edit($id){
            $fitness = Fitness::find($id);
            $this->user_id = $fitness->user_id;
            $this->reminder_item_id = $fitness->reminder_item_id;
            $this->type = $fitness->type;
            $this->issued_at = $fitness->issued_at;
            $this->expires_at =  $fitness->expires_at;
            $this->first_reminder_at = $fitness->first_reminder_at;
            $this->first_reminder_at_status = $fitness->first_reminder_at_status;
            $this->second_reminder_at = $fitness->second_reminder_at;
            $this->second_reminder_at_status = $fitness->second_reminder_at_status;
            $this->third_reminder_at = $this->third_reminder_at;
            $this->third_reminder_at_status = $this->third_reminder_at_status;
            $this->status = $fitness->status;
            $this->cc = $fitness->cc;

            if ($fitness->horse_id) {
                $this->selectedHorse = $fitness->horse_id;
                $this->type = 'Horse';
            }elseif ($fitness->vehicle_id) {
                $this->selectedVehicle = $fitness->vehicle_id;
                $this->type = 'Vehicle';
            }elseif ($fitness->trailer_id) {
                $this->selectedTrailer = $fitness->trailer_id;
                $this->type = 'Trailer';
            }elseif ($fitness->employee_id) {
                $this->selectedEmployee = $fitness->employee_id;
                $this->type = 'Employee';
           
            }else{
                $this->type = 'Other';
            }
            $this->fitness_id = $fitness->id;
            $this->dispatchBrowserEvent('show-fitnessEditModal');
    
            }
    
    
            public function update()
            {
                if ($this->fitness_id) {
                    $fitness = fitness::find($this->fitness_id);
                    $fitness->user_id = Auth::user()->id;
                    $fitness->type = $this->type ;
                    $fitness->horse_id = $this->selectedHorse ? $this->selectedHorse : Null;
                    $fitness->trailer_id = $this->selectedTrailer ? $this->selectedTrailer : Null;
                    $fitness->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : Null;
                    $fitness->employee_id = $this->selectedEmployee ? $this->selectedEmployee : Null;
                    $fitness->reminder_item_id = $this->reminder_item_id;
                    $fitness->issued_at =$this->issued_at;
                    $fitness->expires_at = $this->expires_at;
                    $fitness->cc = $this->cc ;
                    $fitness->expires_at = Carbon::create($this->expires_at)->toDateTimeString();
                    $fitness->first_reminder_at = Carbon::parse($this->expires_at)->subDays(14);
                    $fitness->first_reminder_at_status = $this->first_reminder_at_status;
                    $fitness->second_reminder_at = Carbon::parse($this->expires_at)->subDays(7);
                    $fitness->second_reminder_at_status = $this->second_reminder_at_status;
                    $fitness->third_reminder_at = Carbon::parse($this->expires_at)->subDay();
                    $fitness->third_reminder_at_status = $this->third_reminder_at_status;

                    $today = now()->toDateTimeString();
                    $expire = Carbon::create($this->expires_at)->toDateTimeString();
                    if ($today <=  $expire) {
                        $fitness->status = 1;
                    }else{
                        $fitness->status = 0;
                    }
                    $fitness->update();
    
                    $this->dispatchBrowserEvent('hide-fitnessEditModal');
                    $this->resetInputFields();
                    $this->dispatchBrowserEvent('alert',[
                        'type'=>'success',
                        'message'=>"Reminder Updated Successfully!!"
                    ]);
                }
            }

   
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {

        if (isset($this->searchHorse)) {
            $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')->where('registration_number', 'like', '%'.$this->searchHorse.'%')->get();
        }
        if (isset($this->searchVehicle)) {
            $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('registration_number', 'like', '%'.$this->searchVehicle.'%')->get();
            
        }
        if (isset($this->searchTrailer)) {
            $this->trailers = Trailer::where('registration_number', 'like', '%'.$this->searchTrailer.'%')->get();
        }

        if (isset($this->searchEmployee)) {
            $this->employees = Employee::where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->searchEmployee."%")
            ->get();
        }

        $this->reminder_items = ReminderItem::orderBy('name','asc')->get();
        
        if (isset($this->search) && filled($this->search)) {
         
            return view('livewire.reminders.index',[
                'reminders' => Fitness::where('closed',false)->where('status',true)
                ->where('user_id', Auth::user()->id)
                ->whereHas('reminder_item', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('horse', function ($query) {
                    return $query->where('registration_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('vehicle', function ($query) {
                    return $query->where('registration_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('trailer', function ($query) {
                    return $query->where('registration_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('employee', function ($query) {
                    return $query->where(DB::raw("concat(name, ' ', surname)"), 'like', '%'.$this->search.'%');
                })
                ->orWhereDate('expires_at', 'like', '%'.$this->search.'%')
                ->orWhereDate('issued_at', 'like', '%'.$this->search.'%')
                ->orWhereDate('first_reminder_at', 'like', '%'.$this->search.'%')
                ->orWhereDate('second_reminder_at', 'like', '%'.$this->search.'%')
                ->orWhereDate('third_reminder_at', 'like', '%'.$this->search.'%')
                ->orderBy('created_at','desc')
                ->paginate(10),
                'reminder_items' => $this->reminder_items
            ]);
        }else {
            return view('livewire.reminders.index',[
                'reminders' => Fitness::where('closed',false)->where('status',true)->where('user_id', Auth::user()->id)->orderBy('created_at','desc')->paginate(10),
                'reminder_items' => $this->reminder_items
            ]);
        }

       
    }
}
