<?php

namespace App\Http\Livewire\Trailers;

use Carbon\Carbon;
use App\Models\Cargo;
use App\Models\Trailer;
use Livewire\Component;
use App\Models\Capacity;
use App\Models\Document;
use App\Models\Measurement;
use App\Models\TrailerType;
use App\Models\Transporter;
use App\Models\VehicleType;
use App\Models\TrailerImage;
use App\Models\VehicleImage;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use Livewire\WithFileUploads;
use App\Exports\TrailersExport;
use App\Models\TrailerDocument;
use App\Models\VehicleDocument;
use App\Services\Sage\SageSyncService;
use App\Services\Sage\SageIntegration;
use App\Jobs\Sage\SyncTrailerToSageJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithPagination;
    use \App\Http\Livewire\Concerns\PullsFromSage;

    protected $paginationTheme = 'bootstrap';

    use WithFileUploads;
    public $search;
    protected $queryString = ['search'];

    // Sage sync — selected trailer ids for bulk sync.
    public $sageSelected = [];
    public $measurements;
    public $measurement_id = [];
    public $cargos;
    public $cargo_id = [];
    public $cargo_type;
    public $trailer_types;
    public $trailer_number;
    private $trailers;
    public $transporters;
    public $transporter_id;
    public $trailer_id;
    public $trailer_type_id;
    public $fleet_number;
    public $custom_ref;
    public $make;
    public $model;
    public $nvm;
    public $gvm;
    public $start_date;
    public $end_date;
    public $registration_number;
    public $year;
    public $user_id;
    public $suspension_type;
    public $color;
    public $no_of_wheels;
    public $manufacturer;
    public $capacity = [];
    public $measurement;
    public $status;
    public $origin;
    public $condition;
    public $chasis_number;
    public $compartments;
    public $trailer_capacities;
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

    public $capacity_inputs = [];
    public $c = 1;
    public $d = 1;

    public function capacityAdd($c)
    {
        $c = $c + 1;
        $this->c = $c;
        array_push($this->capacity_inputs ,$c);
    }

    public function capacityRemove($c)
    {
        unset($this->capacity_inputs[$c]);
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
        $this->make = '';
        $this->model = '';
        $this->trailer_type_id = '';
        $this->transporter_id = '';
        $this->chasis_number = '';
        $this->color = '';
        $this->cargo_type = '';
        $this->measurement_id = '';
        $this->cargo_id = '';
        $this->custom_ref = '';
        $this->no_of_wheels = '';
        $this->nvm = '';
        $this->gvm = '';
        $this->origin = '';
        $this->capacity = '';
        $this->condition = '';
        $this->manufacturer = '';
        $this->documents = '';
        $this->start_date = '';
        $this->end_date = '';
        $this->compartments = '';
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

    public function mount(){
        $this->resetPage();
        $this->cargos = Cargo::orderBy('name','asc')->get();
        $this->measurements = Measurement::orderBy('name','asc')->get();
        $this->trailer_types = TrailerType::orderBy('name','asc')->get();
        $this->transporters = Transporter::orderBy('name','asc')->get();
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages =[

      'title.*.required' => 'Title field is required',
      'file.*.required' => 'File field is required',
      'transporter_id.*.required' => 'File field is required',

  ];

  public function deactivate($id){
    $trailer = Trailer::find($id);
    $trailer->status = 0 ;
    $trailer->update();
    Session::flash('success','Trailer successfully deactivated');
    return redirect(route('trailers.manage'));
}

public function activate($id){
    $trailer = Trailer::find($id);
    $trailer->status = 1 ;
    $trailer->update();
    Session::flash('success','Trailer successfully activated');
    return redirect(route('trailers.manage'));
}

    protected $rules = [
     
        'transporter_id' => 'required',
  
        'registration_number' => 'required|unique:vehicles,registration_number,NULL,id,deleted_at,NULL',

       
    ];


    public function store(){
        $trailer = new Trailer;
        $trailer->user_id = Auth::user()->id;
        $trailer->trailer_number = $this->trailerNumber();
        $trailer->fleet_number = $this->fleet_number;
        $trailer->make = $this->make;
        $trailer->model = $this->model;
        $trailer->chasis_number = $this->chasis_number;
        $trailer->registration_number = $this->registration_number;
        $trailer->year = $this->year;
        $trailer->no_of_wheels = $this->no_of_wheels ?? 0;
        $trailer->manufacturer = $this->manufacturer;
        $trailer->custom_ref = $this->custom_ref;
        $trailer->gvm = $this->gvm;
        $trailer->nvm = $this->nvm;
        $trailer->country_of_origin = $this->origin;
        $trailer->color = $this->color;
        $trailer->cargo_type = $this->cargo_type;
        $trailer->start_date = $this->start_date;
        $trailer->end_date = $this->end_date;
        $trailer->suspension_type = $this->suspension_type;
        $trailer->condition = $this->condition;
        $trailer->trailer_type_id = $this->trailer_type_id;
        $trailer->transporter_id = $this->transporter_id;
        $trailer->compartments = $this->compartments;
        $trailer->status = 1;
        $trailer->save();

        if (isset($this->capacity)) {
            foreach ($this->capacity as $key => $value) {
                $capacity = new Capacity;
                $capacity->trailer_id = $trailer->id;
                if (isset($this->cargo_id[$key])) {
                    $capacity->cargo_id = $this->cargo_id[$key];
              
                }
                if (isset($this->measurement_id[$key])) {
                    $capacity->measurement_id = $this->measurement_id[$key];
                }
                if (isset($this->capacity[$key])) {
                   $capacity->capacity = $this->capacity[$key];
                }
              
               
                $capacity->save();
            }
        }

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
              $document = new Document;
              $document->category = "trailer";
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
    }


    public function edit($id){
        $trailer = Trailer::find($id);
        $this->user_id = $trailer->user_id;
        $this->trailer_type_id = $trailer->trailer_type_id;
        $this->transporter_id = $trailer->transporter_id;
        $this->fleet_number = $trailer->fleet_number;
        $this->make = $trailer->make;
        $this->custom_ref = $trailer->custom_ref;
        $this->model = $trailer->model;
        $this->chasis_number = $trailer->chasis_number;
        $this->cargo_type = $trailer->cargo_type;
        $this->year = $trailer->year;
        $this->suspension_type = $trailer->suspension_type;

        $this->trailer_capacities = $trailer->capacities;
        if (isset($this->trailer_capacities)) {
            foreach ( $this->trailer_capacities as $trailer_capacity) {
                $this->capacity[] = $trailer_capacity->capacity;
                $this->measurement_id[] = $trailer_capacity->measurement_id;
                $this->cargo_id[] = $trailer_capacity->cargo_id;
            }
        }
       
        $this->nvm = $trailer->nvm;
        $this->gvm = $trailer->gvm;
        $this->no_of_wheels = $trailer->no_of_wheels;
        $this->color = $trailer->color;
        $this->condition = $trailer->condition;
        $this->start_date = $trailer->start_date;
        $this->end_date = $trailer->end_date;
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
                $trailer->custom_ref = $this->custom_ref;
                $trailer->year = $this->year;
                $trailer->cargo_type = $this->cargo_type;
                $trailer->compartments = $this->compartments;
                $trailer->chasis_number = $this->chasis_number;
                $trailer->color = $this->color;
                $trailer->nvm = $this->nvm;
                $trailer->gvm = $this->gvm;
                $trailer->start_date = $this->start_date;
                $trailer->end_date = $this->end_date;
                $trailer->suspension_type = $this->suspension_type;
                $trailer->condition = $this->condition;
                $trailer->manufacturer = $this->manufacturer;
                $trailer->country_of_origin = $this->origin;
                $trailer->registration_number = $this->registration_number;
                $trailer->status = 1;
                $trailer->update();

                $capacities =  $trailer->capacities;
                if ($capacities->count()>0) {
                    foreach ($capacities as $capacity) {
                        $capacity->delete();
                    }
                }
              

                if (isset($this->capacity)) {
                    foreach ($this->capacity as $key => $value) {
                        $capacity = new Capacity;
                        $capacity->trailer_id = $trailer->id;
                        if (isset($this->cargo_id[$key])) {
                            $capacity->cargo_id = $this->cargo_id[$key];
                        }
                        if (isset($this->measurement_id[$key])) {
                            $capacity->measurement_id = $this->measurement_id[$key];
                        }
                        if (isset($this->capacity[$key])) {
                           $capacity->capacity = $this->capacity[$key];
                        }
                        $capacity->save();
                    }
                }

                $this->dispatchBrowserEvent('hide-trailerEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Trailer Updated Successfully!!"
                ]);

            }
        }

        public function updatingSearch()
        {
            $this->resetPage();
        }

    /** Whether the acting user's company has an active Sage integration. */
    public function getSageEnabledProperty()
    {
        return SageIntegration::enabledForUser();
    }

    /** Sync one trailer to Sage Intacct (Class) inline; also used for retry. */
    public function syncToSage($id)
    {
        if (! $this->sageEnabled) {
            return;
        }

        $trailer = Trailer::findOrFail($id);
        $result  = app(SageSyncService::class)->syncTrailer($trailer);

        $this->dispatchBrowserEvent('alert', [
            'type'    => ! empty($result['success']) ? 'success' : (! empty($result['skipped']) ? 'warning' : 'error'),
            'message' => ! empty($result['success'])
                ? 'Trailer synced to Sage (class ' . ($result['external_id'] ?? '') . ').'
                : 'Sage sync: ' . ($result['error'] ?? 'unknown error'),
        ]);
    }

    public function retrySync($id)
    {
        $this->syncToSage($id);
    }

    /** Bulk sync the selected trailers via queued jobs. */
    public function bulkSyncToSage()
    {
        if (! $this->sageEnabled) {
            return;
        }

        $ids = array_filter($this->sageSelected);

        foreach ($ids as $id) {
            SyncTrailerToSageJob::dispatch((int) $id);
        }

        $this->sageSelected = [];

        $this->dispatchBrowserEvent('alert', [
            'type'    => count($ids) ? 'success' : 'warning',
            'message' => count($ids)
                ? count($ids) . ' trailer(s) queued for Sage sync.'
                : 'Select at least one trailer to sync.',
        ]);
    }

    /** Pull trailers from Sage into Gonyeti (queued, de-duped). */
    public function pullFromSage()
    {
        $this->dispatchSagePull('trailer', 'trailers');
    }

    public function render()
    {
        $this->cargos = Cargo::orderBy('name','asc')->get();
        $this->measurements = Measurement::orderBy('name','asc')->get();
        $this->trailer_types = TrailerType::orderBy('name','asc')->get();
        $this->transporters = Transporter::orderBy('name','asc')->get();

        if (isset($this->search)) {
            return view('livewire.trailers.index',[
                'trailers' => Trailer::with('transporter:id,name')
                ->when($this->sageEnabled, fn ($q) => $q->with('sageMapping'))
                ->where('archive',0)
                ->where('trailer_number','like', '%'.$this->search.'%')
                ->orWhere('registration_number','like', '%'.$this->search.'%')
                ->orWhere('make','like', '%'.$this->search.'%')
                ->orWhere('model','like', '%'.$this->search.'%')
                ->orWhere('fleet_number','like', '%'.$this->search.'%')
                ->orWhereHas('transporter', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('registration_number','asc')->paginate(10),
                'cargos' => $this->cargos,
                'measurements' => $this->measurements,
            ]);
        }else{
            return view('livewire.trailers.index',[
                'trailers' => Trailer::with('transporter:id,name')
                ->when($this->sageEnabled, fn ($q) => $q->with('sageMapping'))
                ->where('archive',0)->orderBy('registration_number','asc')->paginate(10),
                'cargos' => $this->cargos,
                'measurements' => $this->measurements,
                'transporters' => $this->transporters,
                'trailer_types' => $this->trailer_types,
            ]);
        }
    }
}
