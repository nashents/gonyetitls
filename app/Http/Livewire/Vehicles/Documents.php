<?php

namespace App\Http\Livewire\Vehicles;

use Carbon\Carbon;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Document;
use App\Models\Employee;
use Livewire\WithFileUploads;
use App\Models\VehicleDocument;
use Illuminate\Support\Facades\Auth;

class Documents extends Component
{
    use WithFileUploads;

    public $vehicle;
    public $vehicle_id;
    public $user_id;
    public $documents;
    public $document_id;

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
        $this->title = "" ;
        $this->file = "";
        $this->expiry_date = "" ;
    }


    public function mount($id){
    $this->vehicle = Vehicle::find($id);
    $this->documents = VehicleDocument::where('vehicle_id', $this->vehicle->id)->latest()->get();
    }

    public function store(){
        // try{

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
                  $document->vehicle_id = $this->vehicle->id;
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
            $this->dispatchBrowserEvent('hide-documentModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Document(s) Uploaded Successfully!!"
            ]);

        // }catch(\Exception $e){
        //     // Set Flash Message
        //     $this->dispatchBrowserEvent('alert',[
        //         'type'=>'error',
        //         'message'=>"Something went wrong while uploading document(s)!!"
        //     ]);
        // }
    }
    public function edit($id){

        $document = VehicleDocument::find($id);
        $this->user_id = $document->user_id;
        $this->vehicle_id = $document->vehicle_id;
        $this->title = $document->title;
        $this->expiry_date = $document->expiry_date;
        $this->filename = $document->filename;
        $this->document_id = $document->id;

        $this->dispatchBrowserEvent('show-vehicleDocumentEditModal');

        }

        public function update()
        {
            if ($this->document_id) {
                try{
                    if(isset($this->file)){
                        $file = $this->file;
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
                $document = VehicleDocument::find($this->document_id);
                $document->title = $this->title;

                if (isset($fileNameToStore)) {
                    $document->filename = $fileNameToStore;
                }

                $document->vehicle_id = $this->vehicle_id;
                $document->user_id = Auth::user()->id;

                if ($this->expiry_date == null) {
                    $document->expiry_date = null;
                    $document->status = 1;
                }else{
                    $document->expiry_date = $this->expiry_date;
                    $today = date("Y-m-d");
                    $today_time = strtotime($today);
                    $expire_time = strtotime($this->expiry_date);
                    if ($today_time <=  $expire_time) {
                        $document->status = 1;
                    }else{
                        $document->status = 0;
                    }


                }
                $document->update();

                $this->dispatchBrowserEvent('hide-vehicleDocumentEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Document Updated Successfully!!"
                ]);
            }catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something went wrong while updating document(s)!!"
                ]);
            }

            }
        }
    public function render()
    {
        $this->documents = VehicleDocument::where('vehicle_id', $this->vehicle->id)->latest()->get();
        return view('livewire.vehicles.documents',[
            'documents'=> $this->documents
        ]);
    }
}
