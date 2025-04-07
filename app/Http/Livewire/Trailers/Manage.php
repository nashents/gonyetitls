<?php

namespace App\Http\Livewire\Trailers;

use App\Models\Trailer;
use Livewire\Component;
use App\Models\TrailerType;
use App\Models\Transporter;
use App\Models\VehicleImage;
use Maatwebsite\Excel\Excel;
use Livewire\WithFileUploads;
use App\Exports\TrailersExport;
use App\Models\VehicleDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Manage extends Component
{

    use WithFileUploads;

    public $trailer_types;
    public $trailers;
    public $trailer_id;
    public $trailer_type_id;
    public $trailer_number;
    public $fleet_number;
    public $transporters;
    public $transporter_id;
    public $suspension_type;
    public $make;
    public $model;
    public $registration_number;
    public $year;
    public $user_id;
    public $no_of_wheels;
    public $status;
    public $color;
    public $manufacturer;
    public $origin;
    public $condition;
    public $trailer_images;
    public $images = [];

    public $title;
    public $file;

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

    public function exportTrailersCSV(Excel $excel){

        return $excel->download(new TrailersExport, 'trailers.csv', Excel::CSV);
    }
    public function exportTrailersPDF(Excel $excel){

        return $excel->download(new TrailersExport, 'trailers.pdf', Excel::DOMPDF);
    }
    public function exportTrailersExcel(Excel $excel){
        return $excel->download(new TrailersExport, 'trailers.xlsx');
    }


    private function resetInputFields(){
        $this->trailer_number = '';
        $this->make = '';
        $this->model = '';
        $this->trailer_type_id = '';
        $this->color = '';
        $this->no_of_wheels = '';
        $this->origin = '';
        $this->condition = '';
        $this->manufacturer = '';
        $this->registration_number = '';
        $this->year = '';
        $this->color = '';
    }



    public function mount(){
        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names)) {
            $this->trailers = Trailer::with('transporter:id,name')->latest()->get();
        } else {
            $this->trailers = Trailer::with('transporter:id,name')->where('user_id',Auth::user()->id)->latest()->get();
        }
        $this->trailer_types = TrailerType::all();
        $this->transporters = Transporter::orderBy('name','asc')->get();
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages =[

      'title.*.required' => 'Title field is required',
      'file.*.required' => 'File field is required',

  ];
    protected $rules = [
        'fleet_number' => 'required',
        'transporter_id' => 'required',
        'make' => 'nullable',
        'model' => 'required',
        'year' => 'required',
        'color' => 'required',
        'condition' => 'required',
        'registration_number' => 'required|unique:vehicles,registration_number,NULL,id,deleted_at,NULL',
        'origin' => 'required',
        'no_of_wheels' => 'required',
        'manufacturer' => 'required',
        'trailer_type_id' => 'required',
        'images.*' => 'required|image',
        'title.0' => 'nullable|string',
        'file.0' => 'nullable|file|mimes:docx,doc,pdf,xls,xlsx,pptx|max:10000',
        'title.*' => 'required',
        'file.*' => 'required|file|mimes:docx,doc,pdf,xls,xlsx,pptx|max:10000',
    ];
    public function edit($id){
        $trailer = Trailer::find($id);
        $this->trailer_id  = $id;
        $this->trailer_images = $trailer->vehicle_images;
        $this->transporter_id = $trailer->transporter_id;
        $this->trailer_type_id = $trailer->trailer_type_id;
        $this->user_id = $trailer->user_id;
        $this->fleet_number = $trailer->fleet_number;
        $this->make = $trailer->make;
        $this->suspension_type = $trailer->suspension_type;
        $this->model = $trailer->model;
        $this->no_of_wheels = $trailer->no_of_wheels;
        $this->year = $trailer->year;
        $this->color = $trailer->color;
        $this->condition = $trailer->condition;
        $this->manufacturer = $trailer->manufacturer;
        $this->origin = $trailer->country_of_origin;
        $this->registration_number = $trailer->registration_number;
        $this->status = $trailer->status;
        $this->dispatchBrowserEvent('show-trailerEditModal');
    }
    public function update(){
        $trailer = Trailer::find($this->trailer_id);
        $trailer->user_id = Auth::user()->id;
        $trailer->trailer_type_id = $this->trailer_type_id;
        $trailer->fleet_number = $this->fleet_number;
        $trailer->transporter_id = $this->transporter_id;
        $trailer->make = $this->make;
        $trailer->no_of_wheels = $this->no_of_wheels;
        $trailer->model = $this->model;
        $trailer->year = $this->year;
        $trailer->color = $this->color;
        $trailer->suspension_type = $this->suspension_type;
        $trailer->condition = $this->condition;
        $trailer->manufacturer = $this->manufacturer;
        $trailer->country_of_origin = $this->origin;
        $trailer->registration_number = $this->registration_number;
        $trailer->status = 1;
        $trailer->update();

        $this->dispatchBrowserEvent('hide-trailerEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trailer Updated Successfully!!"
        ]);
    }
    public function render()
    {
        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names)) {
            $this->trailers = Trailer::with('transporter:id,name')->latest()->get();
        } else {
            $this->trailers = Trailer::with('transporter:id,name')->where('user_id',Auth::user()->id)->latest()->get();
        }
        return view('livewire.trailers.manage',[
            'trailers' => $this->trailers
        ]);
    }
}
