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
    public $horse_id;
    public $trailers;
    public $trailer_id;
    public $company_id;
    public $vehicles;
    public $vehicle_id;
    public $employees;
    public $employee_id;
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

        $this->horses = collect();

        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->where('archive',0)->get();

        $this->vehicles = collect();

        $this->trailers = collect();
    }

    public function updatedType($value){
        if(!is_null($value)){
            return;
        }

        if ($value == "horse") {
            $this->horses =  Horse::where('archive',0)->orderBy('registration_number','asc')->get();
        }elseif($value == "vehicle"){
            $this->vehicles = Vehicle::where('archive',0)->orderBy('registration_number','asc')->get();
        }elseif($value == "trailer"){
            $this->trailers = Trailer::where('archive',0)->orderBy('registration_number','asc')->get();
        }

    }

    public function store()
    {
        DB::transaction(function () {

            if (empty($this->reminder_item_id)) {
                return;
            }

            $userId    = Auth::id();
            $companyId = Auth::user()->employee?->company_id;

          

                // ----------------------------
                // 1) Resolve type target keys
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
                // 2) Parse dates safely (handles datetime-local: 2025-07-05T12:40)
                // ----------------------------
                $issuedAt  = $this->issued_at  ?? null;
                $expiresAt = $this->expires_at ?? null;

                $issuedAtDb  = $this->parseDateTimeLocalToDb($issuedAt);
                $expiresAtDb = $this->parseDateTimeLocalToDb($expiresAt);

                // Reminders derived from expires_at
                $firstReminder  = $expiresAtDb ? Carbon::parse($expiresAtDb)->subDays(14) : null;
                $secondReminder = $expiresAtDb ? Carbon::parse($expiresAtDb)->subDays(7)  : null;
                $thirdReminder  = $expiresAtDb ? Carbon::parse($expiresAtDb)->subDay()     : null;

                // Active status
                $status = 0;
                if ($expiresAtDb) {
                    $status = now()->lte(Carbon::parse($expiresAtDb)) ? 1 : 0;
                }

                // ----------------------------
                // 3) Build "where" for updateOrCreate
                //    Option A: update by id if supplied (best for edits)
                // ----------------------------
                $fitnessId = $this->fitness_ids ?? null; // add this field on edit if you have it

                $where = $fitnessId
                    ? ['id' => $fitnessId]
                    : array_filter([
                        'company_id'        => $companyId,
                        'reminder_item_id'  => $this->reminder_item_id,
                        // type target (only one of these will be set)
                        'horse_id'          => $targets['horse_id'],
                        'vehicle_id'        => $targets['vehicle_id'],
                        'trailer_id'        => $targets['trailer_id'],
                        'employee_id'       => $targets['employee_id'],
                        
                    ], fn ($v) => $v !== null);

                // ----------------------------
                // 4) Build "update" payload
                // ----------------------------
                $data = [
                    'user_id'   => $userId,
                    'company_id'=> $companyId,

                    'horse_id'    => $targets['horse_id'],
                    'vehicle_id'  => $targets['vehicle_id'],
                    'trailer_id'  => $targets['trailer_id'],
                    'employee_id' => $targets['employee_id'],

                    'reminder_item_id' => $this->reminder_item_id,
                    'cc'               => $this->cc ?? null,

                    'issued_at'  => $issuedAtDb,
                    'expires_at' => $expiresAtDb,

                    'first_reminder_at'        => $firstReminder,
                    'first_reminder_at_status' => 0,

                    'second_reminder_at'        => $secondReminder,
                    'second_reminder_at_status' => 0,

                    'third_reminder_at'        => $thirdReminder,
                    'third_reminder_at_status' => 0,

                    'status' => $status,
                ];

                Fitness::updateOrCreate($where, $data);
          

            $this->dispatchBrowserEvent('hide-fitnessModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => "Reminder Set Successfully!!"
            ]);
        });
    }

    /**
     * Converts either:
     *  - "2025-07-05T12:40" (datetime-local)
     *  - "2025-07-05 12:40:00"
     *  - Carbon/DateTime
     * into "Y-m-d H:i:s" for DB.
     */
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

    public function edit($id){
        $fitness = Fitness::find($id);
        $this->reminder_item_id = $fitness->reminder_item_id;
        $this->issued_at  = $this->dbToDateTimeLocal($fitness->issued_at);
        $this->expires_at = $this->dbToDateTimeLocal($fitness->expires_at);
        $this->first_reminder_at  = $this->dbToDateTimeLocal($fitness->first_reminder_at);
        $this->second_reminder_at = $this->dbToDateTimeLocal($fitness->second_reminder_at);
        $this->third_reminder_at  = $this->dbToDateTimeLocal($fitness->third_reminder_at);
        $this->cc =  $fitness->cc;

        $this->status = $fitness->status;

        if (!is_null($fitness->horse_id)) {
            $this->type = "Horse";
            $this->horse_id = $fitness->horse_id;
        }elseif(!is_null($fitness->vehicle_id)){
            $this->type = "Vehicle";
            $this->vehicle_id = $fitness->vehicle_id;
        }elseif(!is_null($fitness->trailer_id)){
            $this->type = "Trailer";
            $this->trailer_id = $fitness->trailer_id;
        }elseif(!is_null($fitness->employee_id)){
            $this->type = "Employee";
            $this->employee_id = $fitness->employee_id;
        }else{
            $this->type = "Other";
        }
       
    
        $this->fitness_id = $fitness->id;

        $this->horses =  Horse::where('archive',0)->orderBy('registration_number','asc')->get();
        $this->vehicles = Vehicle::where('archive',0)->orderBy('registration_number','asc')->get();
        $this->trailers = Trailer::where('archive',0)->orderBy('registration_number','asc')->get();

        $this->dispatchBrowserEvent('show-fitnessEditModal');

    }


    public function update()
    {
         DB::transaction(function () {

            if (! $this->fitness_id || empty($this->reminder_item_id)) {
                return;
            }
            $item = ReminderItem::find($this->reminder_item_id);
            

            $fitness = Fitness::findOrFail($this->fitness_id);

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
            $firstReminder  = $expiresAtDb ? Carbon::parse($expiresAtDb)->subDays(14) : null;
            $secondReminder = $expiresAtDb ? Carbon::parse($expiresAtDb)->subDays(7)  : null;
            $thirdReminder  = $expiresAtDb ? Carbon::parse($expiresAtDb)->subDay()     : null;

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

   
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {

        if (filled($this->searchHorse)) {
            $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')->where('registration_number', 'like', '%'.$this->searchHorse.'%')->where('archive',0)->get();
        }
        if (filled($this->searchVehicle)) {
            $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('registration_number', 'like', '%'.$this->searchVehicle.'%')->where('archive',0)->get();
            
        }
        if (filled($this->searchTrailer)) {
            $this->trailers = Trailer::where('registration_number', 'like', '%'.$this->searchTrailer.'%')->where('fleet_number', 'like', '%'.$this->searchTrailer.'%')->where('archive',0)->get();
        }

        if (filled($this->searchEmployee)) {
            $this->employees = Employee::where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->searchEmployee."%")->where('archive',0)
            ->get();
        }

        $this->reminder_items = ReminderItem::orderBy('name','asc')->get();
        
        $term = trim((string) $this->search);

        $query = Fitness::query()
            ->with(['reminder_item', 'horse', 'vehicle', 'trailer', 'employee']) // avoid N+1
            ->where('closed', false)
            ->where('status', true)
            ->where('user_id', Auth::id())
            ->when(filled($term), function ($q) use ($term) {
                // Optional: if user types a date like 2026-02-03, search dates properly
                $isDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $term);
                $q->where(function ($q) use ($term, $isDate) {
                    // Relations
                    $q->whereHas('reminder_item', fn ($r) => $r->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('horse', fn ($r) => $r->where('registration_number', 'like', "%{$term}%"))
                    ->orWhereHas('vehicle', fn ($r) => $r->where('registration_number', 'like', "%{$term}%"))
                    ->orWhereHas('trailer', fn ($r) => $r->where('registration_number', 'like', "%{$term}%"))
                    ->orWhereHas('employee', function ($r) use ($term) {
                        $r->whereRaw("concat_ws(' ', name, surname) like ?", ["%{$term}%"]);
                    });
                    // Dates: whereDate does NOT work with "like". Use equality when term is YYYY-MM-DD.
                    if ($isDate) {
                        $q->orWhereDate('expires_at', $term)
                        ->orWhereDate('issued_at', $term)
                        ->orWhereDate('first_reminder_at', $term)
                        ->orWhereDate('second_reminder_at', $term)
                        ->orWhereDate('third_reminder_at', $term);
                    }
                });
            });

        return view('livewire.reminders.index', [
            'reminders'       => $query->orderBy('created_at','desc')->paginate(10),
            'reminder_items'  => $this->reminder_items,
        ]);
       
    }
}
