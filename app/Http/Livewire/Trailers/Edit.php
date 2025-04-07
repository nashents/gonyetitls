<?php

namespace App\Http\Livewire\Trailers;

use App\Models\Trailer;
use Livewire\Component;
use App\Models\TrailerType;
use App\Models\Transporter;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{

    use WithFileUploads;

    public $trailer_types;
    public $trailer_number;
    public $trailers;
    public $transporters;
    public $transporter_id;
    public $trailer_id;
    public $trailer_type_id;
    public $fleet_number;
    public $make;
    public $model;
    public $registration_number;
    public $year;
    public $user_id;
    public $color;
    public $no_of_wheels;
    public $manufacturer;
    public $status;
    public $origin;
    public $condition;
    public $suspension_type;
    public $documents = [];
    public $image;
    public $trailer_images;
    public $images = [];

    public $title;
    public $expires_at;
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

    private function resetInputFields(){
        $this->make = '';
        $this->model = '';
        $this->trailer_type_id = '';
        $this->transporter_id = '';
        $this->color = '';
        $this->no_of_wheels = '';
        $this->origin = '';
        $this->condition = '';
        $this->manufacturer = '';
        $this->documents = '';
        $this->registration_number = '';
        $this->year = '';
        $this->color = '';
    }
   
    protected $rules = [
        'trailer_number' => 'required',
        'transporter_id' => 'required',
        'make' => 'nullable',
        'model' => 'required',
        'year' => 'required',
        'color' => 'required',
        'condition' => 'required',
        'registration_number' => 'required|unique:vehicles,registration_number,NULL,id,deleted_at,NULL',
        'origin' => 'required',
        'manufacturer' => 'required',
        'no_of_wheels' => 'required',
        'trailer_type_id' => 'required',
        'images.*' => 'required|image',
        'title.0' => 'nullable|string',
        'file.0' => 'nullable|file|mimes:docx,doc,pdf,xls,xlsx,pptx|max:10000',
        'title.*' => 'required',
        'file.*' => 'required|file|mimes:docx,doc,pdf,xls,xlsx,pptx|max:10000',
    ];


    public function mount($id){
        $this->trailer_types = TrailerType::orderBy('name','asc')->get();
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $trailer = Trailer::find($id);
        $this->user_id = $trailer->user_id;
        $this->trailer_type_id = $trailer->trailer_type_id;
        $this->transporter_id = $trailer->transporter_id;
        $this->fleet_number = $trailer->fleet_number;
        $this->make = $trailer->make;
        $this->model = $trailer->model;
        $this->year = $trailer->year;
        $this->no_of_wheels = $trailer->no_of_wheels;
        $this->color = $trailer->color;
        $this->condition = $trailer->condition;
        $this->suspension_type = $trailer->suspension_type;
        $this->manufacturer = $trailer->manufacturer;
        $this->registration_number = $trailer->registration_number;
        $this->status = $trailer->status;
        $this->origin = $trailer->country_of_origin;
        $this->trailer_id = $trailer->id;
        $this->images = $trailer->vehicle_images;
    }

    public function update()
    {
        if ($this->trailer_id) {
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

            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Trailer Updated Successfully!!"
            ]);
            return redirect()->route('trailers.manage');

        }
    }
    
    public function render()
    {
        return view('livewire.trailers.edit');
    }
}
