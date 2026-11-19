<?php

namespace App\Http\Livewire\Fitnesses;

use Carbon\Carbon;
use App\Models\Horse;
use App\Models\Fitness;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\ReminderItem;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\FitnessesExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    use WithPagination, \App\Http\Livewire\Concerns\ManagesReminderCopies;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    
    public $status;
    public $name;
    public $reminder_items;
    public $reminder_item_id;
    private $fitnesses;
    public $fitness_id;
    public $issued_at;
    public $number;
    public $expires_at;
    public $cc = False;
    public $reminder_at;
    public $first_reminder_at;
    public $first_reminder_at_status;
    public $second_reminder_at;
    public $second_reminder_at_status;
    public $third_reminder_at;
    public $third_reminder_at_status;
    public $fourth_reminder_at;
    public $fourth_reminder_at_status;
    public $horse_id;
    public $type;
    public $trailer_id;
    public $company_id;
    public $vehicle_id;
    public $filter_id;
    public $employee_id;
    public $customer_id;
    public $vendor_id;

    /** Also email the customer's / vendor's contacts (customer & vendor reminders only). */
    public $notify_contacts = true;

    private function contactsAllowed(): bool
    {
        return in_array($this->type, ['Customer', 'Vendor'], true);
    }

    /** Subject columns for the current type (all others null). */
    private function targets(): array
    {
        $column = Fitness::SUBJECTS[$this->type] ?? null;
        return Fitness::subjectColumns($this->type, $column ? $this->{$column} : null);
    }

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
        $this->cc = false ;
        $this->notify_contacts = true;
        $this->inputs = [];
        $this->resetCopySelection();
    }

    public function exportRemindersCSV(Excel $excel){
        return $excel->download(new FitnessesExport($this->filter_id, $this->type), 'reminders_' .time().'.csv', Excel::CSV);
    }
    public function exportRemindersPDF(Excel $excel){
        return $excel->download(new FitnessesExport($this->filter_id, $this->type), 'reminders_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportRemindersExcel(Excel $excel){
        return $excel->download(new FitnessesExport($this->filter_id, $this->type), 'reminders_' .time().'.xlsx');
    }


    public function mount($id, $type){
        $this->type = $type;
        $this->filter_id = $id;

        if (isset(Fitness::SUBJECTS[$type])) {
            $this->{Fitness::SUBJECTS[$type]} = $id;
        }
        $this->reminder_items = ReminderItem::orderBy('name','asc')->get();
       
       

    }

    public function refresh($category){

        if($category == "reminder_items"){
           $this->reminder_items = ReminderItem::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Reminder Items Refreshed Successfully!!."
            ]);
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

            foreach ($this->reminder_item_id as $key => $reminderItemId) {

                // ----------------------------
                // 1) Resolve type target keys
                // ----------------------------
                $targets = $this->targets();

                // ----------------------------
                // 2) Parse dates safely (handles datetime-local: 2025-07-05T12:40)
                // ----------------------------
                $issuedAt  = $this->issued_at[$key]  ?? null;
                $expiresAt = $this->expires_at[$key] ?? null;

                $issuedAtDb  = $this->parseDateTimeLocalToDb($issuedAt);
                $expiresAtDb = $this->parseDateTimeLocalToDb($expiresAt);

                // Reminders derived from expires_at
                $firstReminder  = $expiresAtDb ? Carbon::parse($expiresAtDb)->subDays(30) : null;
                $secondReminder = $expiresAtDb ? Carbon::parse($expiresAtDb)->subDays(14)  : null;
                $thirdReminder  = $expiresAtDb ? Carbon::parse($expiresAtDb)->subDays(7)     : null;
                $fourthReminder  = $expiresAtDb ? Carbon::parse($expiresAtDb)->subDay()     : null;

                // Active status
                $status = 0;
                if ($expiresAtDb) {
                    $status = now()->lte(Carbon::parse($expiresAtDb)) ? 1 : 0;
                }

                // ----------------------------
                // 3) Build "where" for updateOrCreate
                //    Option A: update by id if supplied (best for edits)
                // ----------------------------
                $fitnessId = $this->fitness_ids[$key] ?? null; // add this field on edit if you have it

                $where = $fitnessId
                    ? ['id' => $fitnessId]
                    : array_filter(array_merge([
                        'company_id'        => $companyId,
                        'reminder_item_id'  => $reminderItemId,
                    ], $targets), fn ($v) => $v !== null); // type target: only one is set

                // ----------------------------
                // 4) Build "update" payload
                // ----------------------------
                $data = $targets + [
                    'user_id'   => $userId,
                    'company_id'=> $companyId,

                    'reminder_item_id' => $reminderItemId,
                    'cc'               => $this->cc[$key] ?? False,
                    'notify_contacts'  => $this->contactsAllowed() && (bool) $this->notify_contacts,

                    'issued_at'  => $issuedAtDb,
                    'expires_at' => $expiresAtDb,

                    'first_reminder_at'        => $firstReminder,
                    'first_reminder_at_status' => 0,

                    'second_reminder_at'        => $secondReminder,
                    'second_reminder_at_status' => 0,

                    'third_reminder_at'        => $thirdReminder,
                    'third_reminder_at_status' => 0,
                   
                    'fourth_reminder_at'        => $fourthReminder,
                    'fourth_reminder_at_status' => 0,

                    'status' => $status,
                ];

                $this->applyCopySelection(Fitness::updateOrCreate($where, $data));
            }

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
        $this->fourth_reminder_at  = $this->dbToDateTimeLocal($fitness->fourth_reminder_at);
        $this->cc =  $fitness->cc;
        $this->notify_contacts = (bool) $fitness->notify_contacts;
        $this->loadCopySelection($fitness);
        $this->status = $fitness->status;
      
        $this->type = $fitness->subjectType();
        if (isset(Fitness::SUBJECTS[$this->type])) {
            $column = Fitness::SUBJECTS[$this->type];
            $this->{$column} = $fitness->{$column};
        }

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
            $targets = $this->targets();

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
            $fitness->update($targets + [
                'user_id'          => Auth::id(),
                'reminder_item_id' => $this->reminder_item_id,
                'cc'               => $this->cc,
                'notify_contacts'  => $this->contactsAllowed() && (bool) $this->notify_contacts,

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

                'status' => $status,
            ]);

            $this->applyCopySelection($fitness);

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
        $this->reminder_items = ReminderItem::orderBy('name','asc')->get();

        $column = Fitness::SUBJECTS[$this->type] ?? null;

        $fitnesses = Fitness::query()
            ->withCount('selectedCopies')
            ->when($column, fn ($q) => $q->where($column, $this->{$column}), fn ($q) => $q->whereRaw('1 = 0'))
            ->where('closed', false)
            ->where('status', true)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.fitnesses.index', [
            'fitnesses' => $fitnesses,
            'reminder_items' => $this->reminder_items,
            'contactRecipients' => $this->contactsAllowed() ? Fitness::contactEmailsFor($this->type, $this->filter_id) : [],
            'copyList' => $this->copyList(),
            'copyEmployees' => $this->copyEmployeeOptions(),
        ]);
    }
}
