<?php

namespace App\Http\Livewire\Horses;

use Carbon\Carbon;
use App\Models\Horse;
use Livewire\Component;
use App\Models\Document;
use App\Models\HorseMake;
use App\Models\HorseType;
use App\Models\HorseGroup;
use App\Models\HorseImage;
use App\Models\HorseModel;
use App\Models\Transporter;
use App\Models\HorseDocument;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class Create extends Component
{
    use WithFileUploads;

    public $horse_type_id;
    public $horse_types;
    public $transporters;
    public $transporter_id;
    public $horse_group_id;
    public $horse_groups;
    public $fleet_number;
    public $horse_model_id;
    public $horse_models;
    public $horse_makes;
    public $selectedMake = Null;
    public $registration_number;
    public $chasis_number;
    public $engine_number;
    public $year;
    public $capacity;
    public $measurement;
    public $color;
    public $no_of_wheels;
    public $start_date;
    public $end_date;
    public $horse_group;
    public $mileage;
    public $hours;
    public $manufacturer;
    public $origin;
    public $condition;
    public $fuel_type;
    public $images = [];
    public $title;
    public $expires_at;
    public $file;
    public $mechanical = False;

    public $fuel_consumption_empty_standard;
    public $fuel_consumption_loaded_standard;

    public $engine_type;
    public $engine_cpl;
    public $gearbox_type;
    public $differential_type;
    public $differential_ratio;
    public $nvm;
    public $gvm;
    public $compressor_size;
    public $compressor_type;
    public $universal_j_size;
    public $rear_spring_type;
    public $front_spring_type;
    public $flange_size;
    public $steering_box_type;
    public $cab_type;
    public $air_dryer_system;
    public $fifth_wheel_type;
    public $starter_type;
    public $starter_size;
    public $alternator_size;
    public $alternator_type;
    public $fuel_filtering_type;
    public $king_pin_type;
    public $fan_belt_type;
    public $fan_belt_size;
    public $water_pump_belt_type;
    public $water_pump_belt_size;
    public $engine_mounting_type;
    public $steering_reservoir;
    public $braking_system_type;
    public $clutch_size;
    public $tnak_hrs;
    public $battery_size;

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
        $this->fleet_number = '';
        $this->selectedMake = '';
        $this->horse_model_id = '';
        $this->transporter_id = '';
        $this->year = '';
        $this->color = '';
        $this->condition = '';
        $this->horse_type_id = '';
        $this->horse_group = '';
        $this->capacity = '';
        $this->measurement = '';
        $this->origin = '';
        $this->mileage = '';
        $this->hours = '';
        $this->manufacturer = '';
        $this->chasis_number = '';
        $this->engine_number = '';
        $this->no_of_wheels = '';
        $this->fuel_type = '';
        $this->start_date = '';
        $this->end_date = '';
        $this->registration_number = '';

    }

    public function horseNumber(){
       
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

            $horse = Horse::orderBy('id', 'desc')->first();

        if (!$horse) {
            $horse_number =  $initials .'H'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $horse->id + 1;
            $horse_number =  $initials .'H'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $horse_number;


    }

    public function mount(){
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->horse_types = HorseType::orderBy('name','asc')->get();
        $this->horse_groups = HorseGroup::orderBy('name','asc')->get();
        $this->horse_makes = HorseMake::orderBy('name','asc')->get();
        $this->horse_models = collect();
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages = [
        'transporter_id.required' => 'Transporter field is required',
  
    ];
      protected $rules = [
        
          'transporter_id' => 'required',
          'chasis_number' => 'nullable|unique:horses,chasis_number,NULL,id,deleted_at,NULL',
          'engine_number' => 'nullable|unique:horses,engine_number,NULL,id,deleted_at,NULL',
          'registration_number' => 'required|unique:horses,registration_number,NULL,id,deleted_at,NULL',
        
      ];

    public function updatedSelectedMake($make)
    {
        if (!is_null($make) ) {
        $this->horse_models = HorseModel::where('horse_make_id', $make)->get();
        }
    }
    public function store(){
        $horse = new Horse;
        $horse->fleet_number = $this->fleet_number;
        $horse->horse_number = $this->horseNumber();
        $horse->horse_make_id = $this->selectedMake;
        $horse->horse_model_id = $this->horse_model_id;
        $horse->transporter_id = $this->transporter_id;
        $horse->registration_number = $this->registration_number;
        $horse->chasis_number = $this->chasis_number;
        $horse->engine_number = $this->engine_number;
        $horse->year = $this->year;
        $horse->manufacturer = $this->manufacturer;
        $horse->country_of_origin = $this->origin;
        $horse->color = $this->color;
        $horse->no_of_wheels = $this->no_of_wheels ? $this->no_of_wheels : "";
        $horse->mileage = $this->mileage;
        $horse->hours = $this->hours;
        $horse->capacity = $this->capacity;
        $horse->measurement = $this->measurement;
        $horse->condition = $this->condition;
        $horse->start_date = $this->start_date;
        $horse->end_date = $this->end_date;
        $horse->fuel_type = $this->fuel_type;
        $horse->gvm = $this->gvm;
        $horse->nvm = $this->nvm;
        $horse->fuel_consumption_empty_standard = $this->fuel_consumption_empty_standard;
        $horse->fuel_consumption_loaded_standard = $this->fuel_consumption_loaded_standard;
        $horse->horse_type_id = $this->horse_type_id;
        $horse->horse_group_id = $this->horse_group_id;
        $horse->engine_type = $this->engine_type;
        $horse->engine_cpl = $this->engine_cpl;
        $horse->gearbox_type = $this->gearbox_type;
        $horse->differential_type = $this->differential_type;
        $horse->differential_ratio = $this->differential_ratio;
        $horse->compressor_size = $this->compressor_size;
        $horse->compressor_type = $this->compressor_type;
        $horse->universal_j_size = $this->universal_j_size;
        $horse->rear_spring_type = $this->rear_spring_type;
        $horse->front_spring_type = $this->front_spring_type;
        $horse->flange_size = $this->flange_size;
        $horse->steering_box_type = $this->steering_box_type;
        $horse->cab_type = $this->cab_type;
        $horse->air_dryer_system = $this->air_dryer_system;
        $horse->fifth_wheel_type = $this->fifth_wheel_type;
        $horse->starter_type = $this->starter_type;
        $horse->starter_size = $this->starter_size;
        $horse->alternator_size = $this->alternator_size;
        $horse->alternator_type = $this->alternator_type;
        $horse->fuel_filtering_type = $this->fuel_filtering_type;
        $horse->king_pin_type = $this->king_pin_type;
        $horse->fan_belt_type = $this->fan_belt_type;
        $horse->fan_belt_size = $this->fan_belt_size;
        $horse->water_pump_belt_type = $this->water_pump_belt_type;
        $horse->water_pump_belt_size = $this->water_pump_belt_size;
        $horse->engine_mounting_type = $this->engine_mounting_type;
        $horse->steering_reservoir = $this->steering_reservoir;
        $horse->braking_system_type = $this->braking_system_type;
        $horse->clutch_size = $this->clutch_size;
        $horse->tnak_hrs = $this->tnak_hrs;
        $horse->battery_size = $this->battery_size;
        $horse->mechanical = $this->mechanical;
        $horse->status = 1;
        $horse->save();
      

        if (isset($this->images)) {

            foreach ($this->images as $image) {

                // get file with ext
                $fileNameWithExt = $image->getClientOriginalName();
                //get filename
                $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                //get extention
                $extention = $image->getClientOriginalExtension();
                //file name to store
                $fileNameToStore= $filename.'_'.time().'.'.$extention;
                $image->storeAs('/uploads', $fileNameToStore, 'path');
                // $image = Image::make($image);
                // $image->stream();
                // Storage::disk('local')->put('public/images/uploads' . '/' . $fileNameToStore, $image, 'public');
                $image = new HorseImage;
                $image->user_id = Auth::user()->id;
                $image->horse_id = $horse->id;
                $image->filename = $fileNameToStore;
                $image->save();
            }

        }
        if (isset($this->file)) {
            foreach ($this->file as $key => $value) {
              if(isset($this->file[$key])){
                  $file = $this->file[$key];
                  // get file with ext
                  $fileNameWithExt = $file->getClientOriginalName();
                  //get filename
                  $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                  //get extention
                  $extention = $file->getClientOriginalExtension();
                  //file name to store
                  $fileNameToStore= $filename.'_'.time().'.'.$extention;
                  $file->storeAs('/documents', $fileNameToStore, 'my_files');

              }
              $document = new Document;
              $document->user_id = Auth::user()->id;
              $document->category = "horse";
              $document->horse_id = $horse->id;
              if(isset($this->title[$key])){
              $document->title = $this->title[$key];
              }
              if (isset($fileNameToStore)) {
                  $document->filename = $fileNameToStore;
              }
              if(isset($this->expires_at[$key])){
                  $document->expires_at = Carbon::create($this->expires_at[$key])->toDateTimeString();
                  $today = now()->toDateTimeString();
                  $expire = Carbon::create($this->expires_at[$key])->toDateTimeString();
                  if ($today <=  $expire) {
                      $document->status = 1;
                  }else{
                      $document->status = 0;
                  }
              }else {
                $document->status = 1;
              }
              $document->save();

            }
                   # code...
          }

        Session::flash('success','Horse Created Successfully !!');
        return redirect()->route('horses.index');
    }
    public function render()
    {
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->horse_types = HorseType::orderBy('name','asc')->get();
        $this->horse_groups = HorseGroup::orderBy('name','asc')->get();
        $this->horse_makes = HorseMake::orderBy('name','asc')->get();
        return view('livewire.horses.create',[
            'transporters' => $this->transporters,
            'horse_makes' => $this->horse_makes,
            'horse_groups' => $this->horse_groups,
            'horse_types' => $this->horse_types
        ]);
    }
}
