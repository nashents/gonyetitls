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
use Illuminate\Support\Facades\DB;
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
    public $fourth_reminder_at;
    public $fourth_reminder_at_status;
    public $horses;
    public $horse_id;
    public $trailers;
    public $trailer_id;
    public $company_id;
    public $vehicles;
    public $vehicle_id;
    public $employees;
    public $employee_id;
    public $cc;
    public $pattern;

    public $searchHorse;
    public $searchVehicle;
    public $searchTrailer;
    public $searchEmployee;
    public $status;
    public $user_id;
    
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
        
        if (!is_null($this->fitness->horse_id)) {
            $this->type = "Horse";
            $this->horse_id = $this->fitness->horse_id;
        }elseif(!is_null($this->fitness->vehicle_id)){
            $this->type = "Vehicle";
            $this->vehicle_id = $this->fitness->vehicle_id;
        }elseif(!is_null($this->fitness->trailer_id)){
            $this->type = "Trailer";
            $this->trailer_id = $this->fitness->trailer_id;
        }elseif(!is_null($this->fitness->employee_id)){
            $this->type = "Employee";
            $this->employee_id = $this->fitness->employee_id;
        }else{
            $this->type = "Other";
        }
        $this->reminder_items = ReminderItem::orderBy('name','asc')->get();

        $this->horses = Horse::orderBy('registration_number','asc')->where('archive',0)->latest()->get();

        $this->employees = Employee::orderBy('name','asc')->where('archive',0)->get();

        $this->vehicles = Vehicle::orderBy('registration_number','asc')->where('archive',0)->latest()->get();

        $this->trailers = Trailer::orderBy('registration_number','asc')->where('archive',0)->latest()->get();

        $this->pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
    }

    private function dbToDateTimeLocal($value): ?string
    {
        if (blank($value)) return null;

        try {
            if ($value instanceof \DateTimeInterface) {
                return Carbon::instance($value)->format('Y-m-d\TH:i');
            }

            return Carbon::parse((string) $value)->format('Y-m-d\TH:i');

        } catch (\Throwable $e) {
            return null;
        }
    }

       private function parseDateTimeLocalToDb($value): ?string
    {
        if (blank($value)) return null;

        try {
            // If it's already Carbon/DateTime-like
            if ($value instanceof \DateTimeInterface) {
                return Carbon::instance($value)->format('Y-m-d H:i:s');
            }

            $value = (string) $value;

            // datetime-local from browser: 2025-07-05T12:40
            if (str_contains($value, 'T')) {
                return Carbon::createFromFormat('Y-m-d\TH:i', $value)->format('Y-m-d H:i:s');
            }

            // fallback parse (works for standard DB strings)
            return Carbon::parse($value)->format('Y-m-d H:i:s');

        } catch (\Throwable $e) {
            // You can log if you want: logger()->warning('Bad datetime', ['value'=>$value]);
            return null;
        }
    }



    public function close($id){
      
        $fitness = Fitness::find($id);

        $fitness->first_reminder_at_status = True;
        $fitness->second_reminder_at_status = True;
        $fitness->third_reminder_at_status = True;
        $fitness->fourth_reminder_at_status = True;
        $fitness->closed = 1;
        $fitness->update();

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Reminder Closed Successfully!!"
        ]);

        return redirect(request()->header('Referer'));

    }

    public function snooze($id)
    {
        $fitness = Fitness::findOrFail($id);

        $today = Carbon::today();

        if ($fitness->first_reminder_at && Carbon::parse($fitness->first_reminder_at)->lte($today)) {
            $fitness->first_reminder_at_status = true;
        }

        if ($fitness->second_reminder_at && Carbon::parse($fitness->second_reminder_at)->lte($today)) {
            $fitness->second_reminder_at_status = true;
        }

        if ($fitness->third_reminder_at && Carbon::parse($fitness->third_reminder_at)->lte($today)) {
            $fitness->third_reminder_at_status = true;
        }

        if ($fitness->fourth_reminder_at && Carbon::parse($fitness->fourth_reminder_at)->lte($today)) {
            $fitness->fourth_reminder_at_status = true;
        }

        // Snooze for 1 day from the exact time button is pressed
        $fitness->snooze_time = Carbon::now()->addDay();

        $fitness->save();

        $snoozeTime = $fitness->snooze_time;

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Reminder Viewed & Snoozed Successfully until ' . $snoozeTime->format('d M Y H:i') . '!!'
        ]);

        return redirect(request()->header('Referer'));
    }

    public function edit($id){
        $fitness = Fitness::find($id);
        $this->user_id = $fitness->user_id;
        $this->reminder_item_id = $fitness->reminder_item_id;
        $this->type = $fitness->type;
        $this->issued_at  = $this->dbToDateTimeLocal($fitness->issued_at);
        $this->expires_at = $this->dbToDateTimeLocal($fitness->expires_at);
        $this->first_reminder_at  = $this->dbToDateTimeLocal($fitness->first_reminder_at);
        $this->second_reminder_at = $this->dbToDateTimeLocal($fitness->second_reminder_at);
        $this->third_reminder_at  = $this->dbToDateTimeLocal($fitness->third_reminder_at);
        $this->fourth_reminder_at  = $this->dbToDateTimeLocal($fitness->fourth_reminder_at);
        $this->status = $fitness->status;
        $this->cc = $fitness->cc;
        $this->horse_id = $fitness->horse_id;
        $this->vehicle_id = $fitness->vehicle_id;
        $this->trailer_id = $fitness->trailer_id;
        $this->employee_id = $fitness->employee_id;
        $this->fitness_id = $fitness->id;
        $this->dispatchBrowserEvent('show-fitnessEditModal');

        }


    public function update()
    {
         DB::transaction(function () {

            if (! $this->fitness_id) {
                return;
            }

          

            $fitness = Fitness::find($this->fitness_id);


            // ----------------------------
            // 1) Resolve target ids (and clear others)
            // ----------------------------
            $targets = [
                'horse_id'    => null,
                'vehicle_id'  => null,
                'trailer_id'  => null,
                'employee_id' => null,
            ];

            if ($this->type === 'Horse') {
                $targets['horse_id'] = $this->horse_id ?? null;
            } elseif ($this->type === 'Vehicle') {
                $targets['vehicle_id'] = $this->vehicle_id ?? null;
            } elseif ($this->type === 'Trailer') {
                $targets['trailer_id'] = $this->trailer_id ?? null;
            } elseif ($this->type === 'Employee') {
                $targets['employee_id'] = $this->employee_id ?? null;
            }

            // ----------------------------
            // 2) Parse dates safely
            // ----------------------------
            $issuedAtDb  = $this->parseDateTimeLocalToDb($this->issued_at);
            $expiresAtDb = $this->parseDateTimeLocalToDb($this->expires_at);

            // Derived reminders from expires_at
            $firstReminder  = $expiresAtDb ? Carbon::parse($expiresAtDb)->subDays(30) : null;
            $secondReminder = $expiresAtDb ? Carbon::parse($expiresAtDb)->subDays(14)  : null;
            $thirdReminder  = $expiresAtDb ? Carbon::parse($expiresAtDb)->subDays(7)     : null;
            $fourthReminder  = $expiresAtDb ? Carbon::parse($expiresAtDb)->subDay()     : null;

            // Status
            $status = 0;
            if ($expiresAtDb) {
                $status = now()->lte(Carbon::parse($expiresAtDb)) ? 1 : 0;
            }

            // ----------------------------
            // 3) Update
            // ----------------------------
            $fitness->update([
                'user_id'          => Auth::id(),
                'reminder_item_id' => $this->reminder_item_id,
                'cc'               => $this->cc,

                'issued_at'        => $issuedAtDb,
                'expires_at'       => $expiresAtDb,

                'first_reminder_at'        => $firstReminder,
                'first_reminder_at_status' => (int) $this->first_reminder_at_status,

                'second_reminder_at'        => $secondReminder,
                'second_reminder_at_status' => (int) $this->second_reminder_at_status,

                'third_reminder_at'        => $thirdReminder,
                'third_reminder_at_status' => (int) $this->third_reminder_at_status,
               
                'fourth_reminder_at'        => $fourthReminder,
                'fourth_reminder_at_status' => (int) $this->fourth_reminder_at_status,

                // clear all then set only the correct one
                'horse_id'    => $targets['horse_id'],
                'vehicle_id'  => $targets['vehicle_id'],
                'trailer_id'  => $targets['trailer_id'],
                'employee_id' => $targets['employee_id'],

                'status' => $status,
            ]);

            $this->dispatchBrowserEvent('hide-fitnessEditModal');
            $this->resetInputFields();

            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => "Reminder Updated Successfully!!"
            ]);
         });
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
