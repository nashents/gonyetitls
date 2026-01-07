<?php

namespace App\Http\Livewire\Vehicles;

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
use Illuminate\Support\Facades\Session;

class Edit extends Component
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
    public $end_date;
    public $selectedMake = Null;
    public $vehicle_models;
    public $vehicle_makes;
    public $registration_number;
    public $chasis_number;
    public $custom_ref;
    public $engine_number;
    public $year;
    public $no_of_wheels;
    public $color;
    public $nvm;
    public $gvm;
    public $mileage;
    public $hours;
    public $manufacturer;
    public $origin;
    public $condition;
    public $vehicle_images;
    public $documents;
    public $fuel_type;
    public $fuel_consumption_empty_standard;
    public $fuel_consumption_loaded_standard;
    public $images = [];
    public $title;
    public $expires_at;
    public $file;
    public $filename;
    public $vehicle_number;
    public $vehicle_id;

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
        $this->vehicle_number = '';
        $this->selectedMake = '';
        $this->vehicle_model_id = '';
        $this->year = '';
        $this->color = '';
        $this->condition = '';
        $this->vehicle_type = '';
        $this->vehicle_group = '';
        $this->custom_ref = '';
        $this->origin = '';
        $this->mileage = '';
        $this->hours = '';
        $this->no_of_wheels = '';
        $this->end_date = '';
        $this->start_date = '';
        $this->manufacturer = '';
        $this->chasis_number = '';
        $this->engine_number = '';
        $this->fuel_type = '';
        $this->registration_number = '';

    }
    public function mount($id){
        $vehicle = Vehicle::find($id);
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->vehicle_id = $id;
        $this->vehicle_types = VehicleType::orderBy('name','asc')->get();
        $this->vehicle_groups = VehicleGroup::orderBy('name','asc')->get();
        $this->vehicle_makes = VehicleMake::orderBy('name','asc')->get();
        $this->vehicle_models = VehicleModel::orderBy('name','asc')->get();
        $this->documents = $vehicle->vehicle_documents;
        $this->vehicle_images = $vehicle->vehicle_images;
        $this->fleet_number = $vehicle->fleet_number;
        $this->registration_number = $vehicle->registration_number;
     
        $this->vehicle_model_id = $vehicle->vehicle_model_id;
        $this->transporter_id = $vehicle->transporter_id;
        $this->selectedMake = $vehicle->vehicle_make_id;
        $this->vehicle_group_id = $vehicle->vehicle_group_id;
        $this->vehicle_type_id = $vehicle->vehicle_type_id;
        $this->condition = $vehicle->condition;
        $this->start_date = $vehicle->start_date;
        $this->gvm = $vehicle->gvm;
        $this->nvm = $vehicle->nvm;
        $this->end_date = $vehicle->end_date;
        $this->year = $vehicle->year;
        $this->no_of_wheels = $vehicle->no_of_wheels;
        $this->manufacturer = $vehicle->manufacturer;
        $this->origin = $vehicle->country_of_origin;
        $this->color = $vehicle->color;
        $this->chasis_number = $vehicle->chasis_number;
        $this->engine_number = $vehicle->engine_number;
        $this->fuel_type = $vehicle->fuel_type;
        $this->fuel_consumption_empty_standard = $vehicle->fuel_consumption_empty_standard;
        $this->fuel_consumption_loaded_standard = $vehicle->fuel_consumption_loaded_standard;
        $this->mileage = $vehicle->mileage;
        $this->hours = $vehicle->hours;
    }

    public function updatedSelectedMake($make)
    {
        if (!is_null($make) ) {
        $this->vehicle_models = VehicleModel::where('vehicle_make_id', $make)->get();
        }
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
        'selectedMake' => 'required',
        'vehicle_model_id' => 'required',
        'registration_number' => 'required|unique:vehicles,registration_number,NULL,id,deleted_at,NULL',
        'images.*' => 'nullable|image',
        'title.0' => 'nullable|string',
        'file.0' => 'nullable|file|mimes:docx,doc,pdf,xls,xlsx,pptx|max:10000',
        'title.*' => 'nullable',
        'file.*' => 'nullable|file|mimes:docx,doc,pdf,xls,xlsx,pptx|max:10000',
    ];

    public function update(){
        $vehicle = Vehicle::find($this->vehicle_id);
        $vehicle->fleet_number = $this->fleet_number;
        $vehicle->vehicle_make_id = $this->selectedMake;
        $vehicle->vehicle_model_id = $this->vehicle_model_id;
        $vehicle->transporter_id = $this->transporter_id;
        $vehicle->registration_number = $this->registration_number;
        $vehicle->chasis_number = $this->chasis_number;
        $vehicle->engine_number = $this->engine_number;
        $vehicle->year = $this->year;
        $vehicle->manufacturer = $this->manufacturer;
        $vehicle->country_of_origin = $this->origin;
        $vehicle->custom_ref = $this->custom_ref;
        $vehicle->color = $this->color;
        $vehicle->gvm = $this->gvm;
        $vehicle->nvm = $this->nvm;
        $vehicle->mileage = $this->mileage;
        $vehicle->hours = $this->hours;
        $vehicle->fuel_consumption_empty_standard = $this->fuel_consumption_empty_standard;
        $vehicle->fuel_consumption_loaded_standard = $this->fuel_consumption_loaded_standard;
        $vehicle->start_date = $this->start_date;
        $vehicle->end_date = $this->end_date;
        $vehicle->no_of_wheels = $this->no_of_wheels;
        $vehicle->condition = $this->condition;
        $vehicle->fuel_type = $this->fuel_type;
        $vehicle->vehicle_type_id = $this->vehicle_type_id;
        $vehicle->vehicle_group_id = $this->vehicle_group_id;
        $vehicle->update();


        Session::flash('success','Vehicle Updated Successfully');
        return redirect()->route('vehicles.index');
    }
    public function render()
    {
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->vehicle_types = VehicleType::latest()->get();
        $this->vehicle_groups = VehicleGroup::latest()->get();
        $this->vehicle_makes = VehicleMake::latest()->get();
        return view('livewire.vehicles.edit',[
            'transporters' => $this->transporters,
            'vehicle_makes' => $this->vehicle_makes,
            'vehicle_groups' => $this->vehicle_groups,
            'vehicle_types' => $this->vehicle_types
        ]);
    }
}
