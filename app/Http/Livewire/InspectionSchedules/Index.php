<?php

namespace App\Http\Livewire\InspectionSchedules;
use App\Models\Asset;
use App\Models\Horse;
use App\Models\InspectionSchedule;
use App\Models\ProblemCategory;
use App\Models\ServiceType;
use App\Models\Trailer;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
   
    private $inspection_schedules;
    public $name;
    public $inspection_schedule_number;
    public $description;
    public $inspection_schedule_id;
    public $user_id;
    public $type;
    public $component;
    public $horses;
    public $selectedHorse;
    public $vehicles;
    public $selectedVehicle;
    public $trailers;
    public $selectedTrailer;
    public $assets;
    public $selectedAsset;
    public $equipment = "Horse";
    public $problem_categories;
    public $problem_category_id;
    public $service_types;
    public $service_type_id;
    public $trigger_type;
    public $interval_km;
    public $interval_days;
    public $last_inspection_date;
    public $last_inspection_km;
    public $next_due_date;
    public $next_due_km;
    public $status;
    public $notes;

    public $searchAsset;
    public $searchHorse;
    public $searchVehicle;
    public $searchTrailer;
    public $searchEmployee;
    public $searchMechanic;
    public $searchVendor;
    public $searchProblem;
    
    protected $queryString = ['searchVehicle','searchAsset','search','searchHorse','searchTrailer'];

    public function mount($type){
        $this->type = $type;
        $this->service_types = ServiceType::orderBy('name','asc')->get();
        $this->problem_categories = ProblemCategory::orderBy('name','asc')->get();
      
    }
   
    public function updated($value){
        $this->validateOnly($value);
    }
    private function resetInputFields(){
        $this->problem_category_id = Null;
        $this->service_type_id = Null;
        $this->selectedHorse = Null;
        $this->selectedVehicle = Null;
        $this->selectedTrailer = Null;
        $this->selectedAsset = Null;
        $this->notes = Null;
        $this->trigger_type = Null;
        $this->interval_km = Null;
        $this->interval_days = Null;
        $this->last_inspection_date = Null;
        $this->last_inspection_km = Null;
        $this->next_due_date = Null;
        $this->next_due_km = Null;
        $this->status = Null;
    }
    protected $rules = [
       'trigger_type' => 'required'
    ];

     public function scheduleNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

        $schedule = InspectionSchedule::latest()->orderBy('id','desc')->first();

        if (!$schedule) {
            $schedule_number =  $initials .'IS'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $schedule->id + 1;
            $schedule_number =  $initials .'IS'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $schedule_number;


    }

    // The form binds selectedHorse/Vehicle/Trailer/Asset; the table stores
    // horse_id/vehicle_id/trailer_id/asset_id. Only the chosen equipment type is kept.
    private function fillEquipment(InspectionSchedule $inspection_schedule){
        $inspection_schedule->horse_id = $this->equipment == "Horse" ? ($this->selectedHorse ?: Null) : Null;
        $inspection_schedule->vehicle_id = $this->equipment == "Vehicle" ? ($this->selectedVehicle ?: Null) : Null;
        $inspection_schedule->trailer_id = $this->equipment == "Trailer" ? ($this->selectedTrailer ?: Null) : Null;
        $inspection_schedule->asset_id = $this->equipment == "Asset" ? ($this->selectedAsset ?: Null) : Null;
    }

    public function store(){
        
            $this->validate();
            
            $inspection_schedule = new InspectionSchedule;
            $inspection_schedule->created_by = Auth::user()->id;
            $inspection_schedule->inspection_schedule_number = $this->scheduleNumber();
            $inspection_schedule->type = $this->type;
            $inspection_schedule->problem_category_id = $this->problem_category_id ?: Null;
            $inspection_schedule->service_type_id = $this->service_type_id ?: Null;
            $this->fillEquipment($inspection_schedule);
            $inspection_schedule->notes = $this->notes;
            $inspection_schedule->trigger_type = $this->trigger_type;
            $inspection_schedule->interval_km = $this->interval_km ?: Null;
            $inspection_schedule->interval_days = $this->interval_days ?: Null;
            $inspection_schedule->last_inspection_date = $this->last_inspection_date ?: Null;
            $inspection_schedule->last_inspection_km = $this->last_inspection_km ?: Null;
            $inspection_schedule->next_due_date = $this->next_due_date ?: Null;
            $inspection_schedule->next_due_km = $this->next_due_km ?: Null;
            $inspection_schedule->status = $this->status ?: 'active';
            $inspection_schedule->save();

            $this->dispatchBrowserEvent('hide-saveModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Schedule Created Successfully!!"
            ]);

      
    }

    public function edit($id){

    $inspection_schedule = InspectionSchedule::find($id);
  
    $this->user_id = $inspection_schedule->created_by;
    $this->service_type_id = $inspection_schedule->service_type_id;
    $this->problem_category_id = $inspection_schedule->problem_category_id;
    $this->inspection_schedule_id = $inspection_schedule->id;

    $this->selectedHorse = $inspection_schedule->horse_id;
    $this->selectedVehicle = $inspection_schedule->vehicle_id;
    $this->selectedTrailer = $inspection_schedule->trailer_id;
    $this->selectedAsset = $inspection_schedule->asset_id;

    if ($inspection_schedule->horse_id) {
        $this->equipment = "Horse";
    }
    elseif ($inspection_schedule->asset_id) {
       $this->equipment = "Asset";
    }
    elseif ($inspection_schedule->vehicle_id) {
       $this->equipment = "Vehicle";
    }
    elseif ($inspection_schedule->trailer_id) {
        $this->equipment = "Trailer";
    }

    $this->dispatchBrowserEvent('show-updateModal');

    }


    public function update()
    {
        if ($this->inspection_schedule_id) {
           
            $inspection_schedule = InspectionSchedule::find($this->inspection_schedule_id);
            $inspection_schedule->type = $this->type;
            $inspection_schedule->problem_category_id = $this->problem_category_id ?: Null;
            $inspection_schedule->service_type_id = $this->service_type_id ?: Null;
            $this->fillEquipment($inspection_schedule);
            $inspection_schedule->notes = $this->notes;
            $inspection_schedule->trigger_type = $this->trigger_type;
            $inspection_schedule->interval_km = $this->interval_km ?: Null;
            $inspection_schedule->interval_days = $this->interval_days ?: Null;
            $inspection_schedule->last_inspection_date = $this->last_inspection_date ?: Null;
            $inspection_schedule->last_inspection_km = $this->last_inspection_km ?: Null;
            $inspection_schedule->next_due_date = $this->next_due_date ?: Null;
            $inspection_schedule->next_due_km = $this->next_due_km ?: Null;
            $inspection_schedule->status = $this->status ?: 'active';
            $inspection_schedule->update();

            $this->dispatchBrowserEvent('hide-updateModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Schedule Updated Successfully!!"
            ]);
            // return redirect()->route('inspection_schedules.index');
        
        }
    }


    public function render()
    {

     if (filled($this->searchHorse)) {
            $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')->where('registration_number', 'like', '%'.$this->searchHorse.'%')->get();
        }else{
            $this->horses = Horse::with('horse_make:id,name','horse_model:id,name')->orderByIdentifier('asc')->get();
        }
          if (filled($this->searchVehicle)) {
            $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('registration_number', 'like', '%'.$this->searchVehicle.'%')->get();
        }else{
              $this->vehicles = Vehicle::with('vehicle_make:id,name','vehicle_model:id,name')->orderByIdentifier('asc')->get();
        }
         if (filled($this->searchAsset)) {
            $this->assets = Asset::query()->with('product:id,name','product.brand')->where('disposed', 0)->where('status', 1)
            ->where('serial_number', 'like', '%'.$this->searchAsset.'%')
            ->orWhereHas('product', function ($query) {
                    return $query->where('name', 'like', '%'.$this->searchAsset.'%');
            })
             ->orWhereHas('product.brand', function ($query) {
                    return $query->where('name', 'like', '%'.$this->searchAsset.'%');
            })
            ->get();
            
        }else{
            $this->assets = Asset::with('product')->where('disposed', 0)->where('status', 1)->get()->sortBy('product.name');
        }

        if (filled($this->searchTrailer)) {
            $this->trailers = Trailer::where('registration_number', 'like', '%'.$this->searchTrailer.'%')->get();
        }else{
            $this->trailers = Trailer::orderByIdentifier('asc')->get();
        }
        $search = trim((string) $this->search);

        $query = InspectionSchedule::query()
            ->where('type', $this->type)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('inspection_schedule_number', 'like', "%{$search}%")
                    ->orWhere('trigger_type', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
                });
            })
        ->orderBy('id', 'desc')
        ->paginate(10);

        return view('livewire.inspection-schedules.index',[
            'inspection_schedules' => $query
        ]);
    }
}
