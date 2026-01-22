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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    use WithPagination;
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
    public $cc = false;
    public $reminder_at;
    public $first_reminder_at;
    public $first_reminder_at_status;
    public $second_reminder_at;
    public $second_reminder_at_status;
    public $third_reminder_at;
    public $third_reminder_at_status;
    public $horse_id;
    public $category;
    public $trailer_id;
    public $company_id;
    public $vehicle_id;
    public $filter_id;
    public $employee_id;

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
        $this->inputs = [];
    }

    public function exportRemindersCSV(Excel $excel){
        return $excel->download(new FitnessesExport($this->filter_id, $this->category), 'reminders_' .time().'.csv', Excel::CSV);
    }
    public function exportRemindersPDF(Excel $excel){
        return $excel->download(new FitnessesExport($this->filter_id, $this->category), 'reminders_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportRemindersExcel(Excel $excel){
        return $excel->download(new FitnessesExport($this->filter_id, $this->category), 'reminders_' .time().'.xlsx');
    }


    public function mount($id, $category){
        $this->category = $category;
        $this->filter_id = $id;
        if ($category == "Horse") {
            $this->horse_id = $id;
        }elseif($category == "Vehicle"){
            $this->vehicle_id = $id;
        }
        elseif($category == "Trailer"){
            $this->trailer_id = $id;
        }
        elseif($category == "Employee"){
            $this->employee_id = $id;
        }
        $this->reminder_items = ReminderItem::orderBy('name','asc')->get();
       
       

    }
    public function store(){
    // try{

        if (isset($this->reminder_item_id)) {
        foreach ($this->reminder_item_id as $key => $value) {
        $fitness = new Fitness;
        $fitness->user_id = Auth::user()->id;
        $fitness->company_id = Auth::user()->employee ? Auth::user()->employee->company_id : "";
        if (isset($this->horse_id) && $this->category == "Horse") {
            $fitness->horse_id = $this->horse_id;
        }
        if (isset($this->vehicle_id) && $this->category == "Vehicle") {
            $fitness->vehicle_id = $this->vehicle_id;
        }
        if (isset( $this->trailer_id) && $this->category == "Trailer") {
            $fitness->trailer_id = $this->trailer_id;
        }
      
        if (isset( $this->employee_id) && $this->category == "Employee") {
            $fitness->employee_id = $this->employee_id;
        }

        if (isset($this->reminder_item_id[$key])) {
             $fitness->reminder_item_id = $this->reminder_item_id[$key];
        }
        if (isset($this->cc[$key])) {
             $fitness->cc = $this->cc[$key];
        }

        if (isset($this->issued_at[$key])) {
              $fitness->issued_at = Carbon::create($this->issued_at[$key])->toDateTimeString();
        }
        if (isset($this->expires_at[$key])) {
           $fitness->expires_at = Carbon::create($this->expires_at[$key])->toDateTimeString();
           $fitness->first_reminder_at = Carbon::parse( $this->expires_at[$key])->subDays(14);
           $fitness->first_reminder_at_status = 0;
           $fitness->second_reminder_at = Carbon::parse( $this->expires_at[$key])->subDays(7);
           $fitness->second_reminder_at_status = 0;
           $fitness->third_reminder_at = Carbon::parse( $this->expires_at[$key])->subDay();
           $fitness->third_reminder_at_status = 0;
        }
      
        $today = now()->toDateTimeString();
        if (isset($this->expires_at[$key])) {
            $expire = Carbon::create($this->expires_at[$key])->toDateTimeString();
        }
       
        if ($today <=  $expire) {
            $fitness->status = 1;
        }else{
            $fitness->status = 0;
        }
        $fitness->save();
            }
        }

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
        $this->reminder_item_id = $fitness->reminder_item_id;
        $this->issued_at = $fitness->issued_at;
        $this->expires_at =  $fitness->expires_at;
        $this->cc =  $fitness->cc;
        $this->first_reminder_at = $fitness->first_reminder_at;
        $this->first_reminder_at_status = $fitness->first_reminder_at_status;
        $this->second_reminder_at = $fitness->second_reminder_at;
        $this->second_reminder_at_status = $fitness->second_reminder_at_status;
        $this->third_reminder_at = $this->third_reminder_at;
        $this->third_reminder_at_status = $this->third_reminder_at_status;
        $this->status = $fitness->status;
        $this->horse_id = $fitness->horse_id;
        $this->vehicle_id = $fitness->vehicle_id;
        $this->trailer_id = $fitness->trailer_id;
        $this->employee_id = $fitness->employee_id;
        $this->fitness_id = $fitness->id;
        $this->dispatchBrowserEvent('show-fitnessEditModal');

        }


        public function update()
        {
            if ($this->fitness_id) {
                $fitness = Fitness::find($this->fitness_id);
                $fitness->user_id = Auth::user()->id;
                $fitness->reminder_item_id = $this->reminder_item_id;
                $fitness->issued_at =$this->issued_at;
                $fitness->expires_at = $this->expires_at;
                $fitness->cc = $this->cc;
                $fitness->expires_at = Carbon::create($this->expires_at)->toDateTimeString();
                $fitness->first_reminder_at = Carbon::parse($this->expires_at)->subDays(14);
                $fitness->first_reminder_at_status = $this->first_reminder_at_status;
                $fitness->second_reminder_at = Carbon::parse($this->expires_at)->subDays(7);
                $fitness->second_reminder_at_status = $this->second_reminder_at_status;
                $fitness->third_reminder_at = Carbon::parse($this->expires_at)->subDay();
                $fitness->third_reminder_at_status = $this->third_reminder_at_status;

                if (isset($this->horse_id) && $this->category == "Horse") {
                    $fitness->horse_id = $this->horse_id;
                }
                if (isset($this->vehicle_id) && $this->category == "Vehicle") {
                    $fitness->vehicle_id = $this->vehicle_id;
                }
                if (isset( $this->trailer_id) && $this->category == "Trailer") {
                    $fitness->trailer_id = $this->trailer_id;
                }
                if (isset( $this->employee_id) && $this->category == "Employee") {
                    $fitness->employee_id = $this->employee_id;
                }

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
        $this->reminder_items = ReminderItem::orderBy('name','asc')->get();

        if (isset($this->category) && $this->category == "Horse") {
            return view('livewire.fitnesses.index',[
                'fitnesses' => Fitness::where('horse_id', $this->horse_id)->where('closed',false)->where('status',true)->orderBy('created_at','desc')->paginate(10),
                'reminder_items' => $this->reminder_items
            ]);
        }elseif (isset($this->category) && $this->category == "Vehicle") {
            return view('livewire.fitnesses.index',[
                'fitnesses' =>  Fitness::where('vehicle_id', $this->vehicle_id)->where('closed',false)->where('status',true)->orderBy('created_at','desc')->paginate(10),
                'reminder_items' => $this->reminder_items
            ]);
        
        }
        elseif (isset($this->category) && $this->category == "Trailer") {
            return view('livewire.fitnesses.index',[
                'fitnesses' => Fitness::where('trailer_id', $this->trailer_id)->where('closed',false)->where('status',true)->orderBy('created_at','desc')->paginate(10),
                'reminder_items' => $this->reminder_items
            ]);
          
        }
        elseif (isset($this->category) && $this->category == "Employee") {
            return view('livewire.fitnesses.index',[
                'fitnesses' => Fitness::where('employee_id', $this->employee_id)->where('closed',false)->where('status',true)->orderBy('created_at','desc')->paginate(10),
                'reminder_items' => $this->reminder_items
            ]);
          
        }
       
        
        
    }
}
