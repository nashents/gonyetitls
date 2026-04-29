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
        $this->type = "";
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

    public function store(){
        
            $this->validate();
            
            $inspection_schedule = new InspectionSchedule;
            $inspection_schedule->created_by = Auth::user()->id;
            $inspection_schedule->inspection_schedule_number = $this->scheduleNumber();
            $inspection_schedule->type = $this->type;
            $inspection_schedule->problem_category_id = $this->problem_category_id ?: Null;
            $inspection_schedule->service_type_id = $this->service_type_id ?: Null;
            $inspection_schedule->selectedHorse = $this->selectedHorse ?: Null;
            $inspection_schedule->selectedVehicle = $this->selectedVehicle ?: Null;
            $inspection_schedule->selectedTrailer = $this->selectedTrailer ?: Null;
            $inspection_schedule->selectedAsset = $this->selectedAsset ?: Null;
            $inspection_schedule->notes = $this->notes;
            $inspection_schedule->trigger_type = $this->trigger_type;
            $inspection_schedule->interval_km = $this->interval_km;
            $inspection_schedule->interval_days = $this->interval_days;
            $inspection_schedule->last_inspection_date = $this->last_inspection_date;
            $inspection_schedule->last_inspection_km = $this->last_inspection_km;
            $inspection_schedule->next_due_date = $this->next_due_date;
            $inspection_schedule->next_due_km = $this->next_due_km;
            $inspection_schedule->status = $this->status;
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

    if ($inspection_schedule->selectedHorse) {
        $this->equipment = "Horse";
    }
    elseif ($inspection_schedule->selectedAsset) {
       $this->equipment = "Asset";
    }
    elseif ($inspection_schedule->selectedVehicle) {
       $this->equipment = "Vehicle";
    }
    elseif ($inspection_schedule->selectedTrailer) {
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
            $inspection_schedule->selectedHorse = $this->selectedHorse ?: Null;
            $inspection_schedule->selectedVehicle = $this->selectedVehicle ?: Null;
            $inspection_schedule->selectedTrailer = $this->selectedTrailer ?: Null;
            $inspection_schedule->selectedAsset = $this->selectedAsset ?: Null;
            $inspection_schedule->notes = $this->notes;
            $inspection_schedule->trigger_type = $this->trigger_type;
            $inspection_schedule->interval_km = $this->interval_km;
            $inspection_schedule->interval_days = $this->interval_days;
            $inspection_schedule->last_inspection_date = $this->last_inspection_date;
            $inspection_schedule->last_inspection_km = $this->last_inspection_km;
            $inspection_schedule->next_due_date = $this->next_due_date;
            $inspection_schedule->next_due_km = $this->next_due_km;
            $inspection_schedule->status = $this->status;
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
            $this->horses = Horse::with('horse_make:id,name','horse_model:id,name')->orderBy('registration_number','asc')->get();
        }
          if (filled($this->searchVehicle)) {
            $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('registration_number', 'like', '%'.$this->searchVehicle.'%')->get();
        }else{
              $this->vehicles = Vehicle::with('vehicle_make:id,name','vehicle_model:id,name')->orderBy('registration_number','asc')->get();
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
            $this->trailers = Trailer::orderBy('registration_number','asc')->get();
        }
        $search = trim($this->search);

        $query = InspectionSchedule::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('unit_of_measure', 'like', "%{$search}%");
                });
            })
        ->orderBy('name', 'asc')
        ->paginate(10);

        return view('livewire.inspection-schedules.index',[
            'inspection_schedules' => $query
        ]);
    }
}
