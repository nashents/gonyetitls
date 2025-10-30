<?php

namespace App\Http\Livewire\Vehicles;

use Carbon\Carbon;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Transporter;
use App\Models\VehicleMake;
use App\Models\VehicleType;
use App\Models\VehicleGroup;
use App\Models\VehicleImage;
use App\Models\VehicleModel;
use Livewire\WithFileUploads;
use App\Models\VehicleDocument;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class Create extends Component
{
    use WithFileUploads;

    public $vehicle_type;
    public $vehicle_type_id;
    public $vehicle_types;
    public $vehicle_group;
    public $vehicle_group_id;
    public $vehicle_groups;
    public $transporters;
    public $transporter_id;
    public $fleet_number;
    public $vehicle_model_id;
    public $start_date;
    public $nvm;
    public $gvm;
    public $end_date;
    public $selectedMake = Null;
    public $vehicle_models;
    public $vehicle_makes;
    public $registration_number;
    public $chasis_number;
    public $engine_number;
    public $year;
    public $color;
    public $no_of_wheels;
    public $mileage;
    public $hours;
    public $manufacturer;
    public $origin;
    public $condition;
    public $fuel_type;
    public $fuel_consumption_empty_standard;
    public $fuel_consumption_loaded_standard;

    public $images = [];
    public $title;
    public $expires_at;
    public $file;
    public $filename;

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
        $this->transporter_id = '';
        $this->fleet_number = '';
        $this->selectedMake = '';
        $this->vehicle_model_id = '';
        $this->year = '';
        $this->color = '';
        $this->start_date = '';
        $this->end_date = '';
        $this->condition = '';
        $this->vehicle_type = '';
        $this->vehicle_group = '';
        $this->origin = '';
        $this->mileage = '';
        $this->hours = '';
        $this->no_of_wheels = '';
        $this->manufacturer = '';
        $this->chasis_number = '';
        $this->engine_number = '';
        $this->fuel_type = '';
        $this->registration_number = '';
    

    }
    public function mount(){
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->vehicle_types = VehicleType::orderBy('name','asc')->get();
        $this->vehicle_groups = VehicleGroup::orderBy('name','asc')->get();
        $this->vehicle_makes = VehicleMake::orderBy('name','asc')->get();
        $this->vehicle_models = collect();
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages =[

      'title.*.required' => 'Title field is required',
      'file.*.required' => 'File field is required',
      'department_id.required' => 'Select Department',
      'branch_id.required' => 'Select Branch',
      'role_id.required' => 'Select Role',
      'transporter_id.required' => 'Select Transporter',

  ];
    protected $rules = [
        'transporter_id' => 'required',
        'fleet_number' => 'nullable',
        'selectedMake' => 'required',
        'vehicle_model_id' => 'required',
        'registration_number' => 'required|unique:vehicles,registration_number,NULL,id,deleted_at,NULL',
        
        'images.*' => 'nullable|image',
        'title.0' => 'nullable|string',
        'file.0' => 'nullable|file|mimes:docx,doc,pdf,xls,xlsx,pptx|max:10000',
        'title.*' => 'nullable',
        'file.*' => 'nullable|file|mimes:docx,doc,pdf,xls,xlsx,pptx|max:10000',
    ];

     public function updatedSelectedMake($make)
    {
        if (!is_null($make) ) {
        $this->vehicle_models = VehicleModel::where('vehicle_make_id', $make)->get();
        }
    }

    public function vehicleNumber(){

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

            $vehicle = Vehicle::orderBy('id','desc')->first();

        if (!$vehicle) {
            $vehicle_number =  $initials .'V'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $vehicle->id + 1;
            $vehicle_number =  $initials .'V'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $vehicle_number;


    }
    public function store(){
        $vehicle = new Vehicle;
        $vehicle->vehicle_number = $this->vehicleNumber();
        $vehicle->fleet_number = $this->fleet_number;
        $vehicle->user_id = Auth::user()->id;
        $vehicle->vehicle_make_id = $this->selectedMake;
        $vehicle->vehicle_model_id = $this->vehicle_model_id;
        $vehicle->registration_number = $this->registration_number;
        $vehicle->chasis_number = $this->chasis_number;
        $vehicle->engine_number = $this->engine_number;
        $vehicle->transporter_id = $this->transporter_id;
        $vehicle->year = $this->year;
        $vehicle->gvm = $this->gvm;
        $vehicle->nvm = $this->nvm;
        $vehicle->start_date = $this->start_date;
        $vehicle->end_date = $this->end_date;
        $vehicle->fuel_consumption_empty_standard = $this->fuel_consumption_empty_standard;
        $vehicle->fuel_consumption_loaded_standard = $this->fuel_consumption_loaded_standard;
        $vehicle->manufacturer = $this->manufacturer;
        $vehicle->country_of_origin = $this->origin;
        $vehicle->color = $this->color;
        $vehicle->no_of_wheels = $this->no_of_wheels;
        $vehicle->mileage = $this->mileage;
        $vehicle->hours = $this->hours;
        $vehicle->condition = $this->condition;
        $vehicle->fuel_type = $this->fuel_type;
        $vehicle->vehicle_type_id = $this->vehicle_type_id;
        $vehicle->vehicle_group_id = $this->vehicle_group_id;
        $vehicle->status = 1;
        $vehicle->service = 0;
        $vehicle->save();

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
                $image = new VehicleImage;
                $image->user_id = Auth::user()->id;
                $image->vehicle_id = $vehicle->id;
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
              $document = new VehicleDocument;
              $document->user_id = Auth::user()->id;
              $document->vehicle_id = $vehicle->id;
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

        Session::flash('success','Vehicle successfully created');
        return redirect()->route('vehicles.index');
    }
    public function render()
    {
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->vehicle_types = VehicleType::orderBy('name','asc')->get();
        $this->vehicle_groups = VehicleGroup::orderBy('name','asc')->get();
        $this->vehicle_makes = VehicleMake::orderBy('name','asc')->get();
        return view('livewire.vehicles.create',[
            'transporters' => $this->transporters,
            'vehicle_makes' => $this->vehicle_makes,
            'vehicle_groups' => $this->vehicle_groups,
            'vehicle_types' => $this->vehicle_types
        ]);
    }
}
