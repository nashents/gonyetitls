<?php

namespace App\Http\Livewire\Transporters;

use Carbon\Carbon;
use App\Models\Trailer;
use Livewire\Component;
use App\Models\TrailerType;
use App\Models\Transporter;
use App\Models\TrailerImage;
use Livewire\WithFileUploads;
use App\Models\TrailerDocument;
use Illuminate\Support\Facades\Auth;

class Trailers extends Component
{

    public $trailers;
    public $transporter;
    public $transporter_id;
    public $trailer_types;

    use WithFileUploads;


    public $trailer_number;

    public $transporters;

    public $trailer_id;
    public $trailer_type_id;
    public $fleet_number;
    public $make;
    public $model;
    public $registration_number;
    public $year;
    public $user_id;
    public $suspension_type;
    public $color;
    public $no_of_wheels;
    public $manufacturer;
    public $status;
    public $origin;
    public $condition;
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
   
    public function trailerNumber(){

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

            $trailer = Trailer::orderBy('id', 'desc')->first();

        if (!$trailer) {
            $trailer_number =  $initials .'T'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $trailer->id + 1;
            $trailer_number =  $initials .'T'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $trailer_number;


    }

    public function mount($id){
        $this->transporter_id = $id;
        $this->transporter = Transporter::find($id);
        $this->trailer_types = TrailerType::all();
        $this->trailers = $this->transporter->trailers;
    }


    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages =[

      'title.*.required' => 'Title field is required',
      'file.*.required' => 'File field is required',
      'transporter_id.*.required' => 'File field is required',

  ];

//   public function deactivate($id){
//     $trailer = Trailer::find($id);
//     $trailer->status = 0 ;
//     $trailer->update();
//     Session::flash('success','Trailer successfully deactivated');
//     return redirect(route('trailers.index'));
// }

// public function activate($id){
//     $trailer = Trailer::find($id);
//     $trailer->status = 1 ;
//     $trailer->update();
//     Session::flash('success','Trailer successfully activated');
//     return redirect(route('trailers.index'));
// }

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


    public function store(){
        $trailer = new Trailer;
        $trailer->user_id = Auth::user()->id;
        $trailer->trailer_number = $this->trailerNumber();
        $trailer->fleet_number = $this->fleet_number;
        $trailer->make = $this->make;
        $trailer->model = $this->model;
        $trailer->registration_number = $this->registration_number;
        $trailer->year = $this->year;
        $trailer->no_of_wheels = $this->no_of_wheels;
        $trailer->manufacturer = $this->manufacturer;
        $trailer->country_of_origin = $this->origin;
        $trailer->color = $this->color;
        $trailer->suspension_type = $this->suspension_type;
        $trailer->condition = $this->condition;
        $trailer->trailer_type_id = $this->trailer_type_id;
        $trailer->transporter_id = $this->transporter_id;
        $trailer->status = 1;
        $trailer->save();

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

                $image = new TrailerImage;
                $image->user_id = Auth::user()->id;
                $image->trailer_id = $trailer->id;
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
              $document = new TrailerDocument;
              $document->user_id = Auth::user()->id;
              $document->trailer_id = $trailer->id;
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

        $this->dispatchBrowserEvent('hide-trailerModal');
  
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trailer Created Successfully!!"
        ]);
        return redirect(request()->header('Referer'));
    }

    public function edit($id){
        $trailer = Trailer::find($id);
        $this->user_id = $trailer->user_id;
        $this->trailer_type_id = $trailer->trailer_type_id;
        $this->transporter_id = $trailer->transporter_id;
        $this->fleet_number = $trailer->fleet_number;
        $this->make = $trailer->make;
        $this->model = $trailer->model;
        $this->year = $trailer->year;
        $this->suspension_type = $trailer->suspension_type;
        $this->no_of_wheels = $trailer->no_of_wheels;
        $this->color = $trailer->color;
        $this->condition = $trailer->condition;
        $this->manufacturer = $trailer->manufacturer;
        $this->registration_number = $trailer->registration_number;
        $this->status = $trailer->status;
        $this->origin = $trailer->country_of_origin;
        $this->trailer_id = $trailer->id;

        $this->images = $trailer->vehicle_images;

        $this->dispatchBrowserEvent('show-trailerEditModal');

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
                $this->dispatchBrowserEvent('hide-trailerEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Trailer Updated Successfully!!"
                ]);
                return redirect(request()->header('Referer'));

            }
        }

    public function render()
    {
        $this->trailers = $this->transporter->trailers;
        return view('livewire.transporters.trailers',[
            'trailers' => $this->trailers
        ]);
    }
}
