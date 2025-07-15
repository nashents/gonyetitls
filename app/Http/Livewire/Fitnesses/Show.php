<?php

namespace App\Http\Livewire\Fitnesses;

use Carbon\Carbon;
use App\Models\Horse;
use App\Models\Fitness;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Employee;
use App\Models\ReminderItem;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{

    public $fitness;
    public $reminder_items;

    public $type = Null;
    public $name;
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
    public $pattern;

    public $searchHorse;
    public $searchVehicle;
    public $searchTrailer;
    public $searchEmployee;
    
    protected $queryString = ['searchVehicle','searchHorse','searchTrailer','searchEmployee'];

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
        $this->inputs = [];
    }

    public function mount($id){
        $this->fitness_id = $id;
        $this->fitness = Fitness::find($id); 
        $this->reminder_items = ReminderItem::orderBy('name','asc')->get();

        $this->horses = Horse::orderBy('registration_number','asc')->where('status',1)->latest()->get();

        $this->employees = Employee::orderBy('name','asc')->where('status',1)->get();

        $this->vehicles = Vehicle::orderBy('registration_number','asc')->where('status',1)->latest()->get();

        $this->trailers = Trailer::orderBy('registration_number','asc')->where('status',1)->latest()->get();

        $this->pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
    }



    public function close($id){
      
        $fitness = Fitness::find($id);

        $fitness->first_reminder_at_status = True;
        $fitness->second_reminder_at_status = True;
        $fitness->third_reminder_at_status = True;
        $fitness->closed = 1;
        $fitness->update();

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Reminder Closed Successfully!!"
        ]);

        return redirect(request()->header('Referer'));

    }

    public function snooze($id){
      
        $fitness = Fitness::find($id);

        if ($fitness->first_reminder_at <=  Carbon::today() ) {
            $fitness->first_reminder_at_status = True;
           
        }
        if ($fitness->second_reminder_at <=  Carbon::today() ) {
            $fitness->second_reminder_at_status = True;
            
        }
        if ($fitness->third_reminder_at <=  Carbon::today() ) {
            $fitness->third_reminder_at_status = True;
           
        }
        $fitness->update();

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Reminder Viewed & Snoozed Successfully!!"
        ]);

        return redirect(request()->header('Referer'));

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
        $this->selectedHorse = $fitness->horse_id;
        $this->selectedVehicle = $fitness->vehicle_id;
        $this->selectedTrailer = $fitness->trailer_id;
        $this->selectedEmployee = $fitness->employee_id;
        $this->fitness_id = $fitness->id;
        $this->dispatchBrowserEvent('show-fitnessEditModal');

        }


        public function update()
        {
            if ($this->fitness_id) {
                $fitness = Fitness::find($this->fitness_id);
                $fitness->user_id = Auth::user()->id;
                $fitness->type = $this->type ;
                $fitness->horse_id = $this->selectedHorse ? $this->selectedHorse : Null;
                $fitness->trailer_id = $this->selectedTrailer ? $this->selectedTrailer : Null;
                $fitness->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : Null;
                $fitness->employee_id = $this->selectedEmployee ? $this->selectedEmployee : Null;
                $fitness->reminder_item_id = $this->reminder_item_id;
                $fitness->issued_at =$this->issued_at;
                $fitness->expires_at = $this->expires_at;
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

        $this->fitness = Fitness::find($this->fitness_id); 

        return view('livewire.fitnesses.show',[
            'fitness' => $this->fitness
        ]);
    }
}
