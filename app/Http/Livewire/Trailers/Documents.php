<?php

namespace App\Http\Livewire\Trailers;

use Carbon\Carbon;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Document;
use App\Models\Employee;
use Livewire\WithFileUploads;
use App\Models\TrailerDocument;
use Illuminate\Support\Facades\Auth;

class Documents extends Component
{
    use WithFileUploads;

    public $trailer;
    public $trailer_id;
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
        $this->expires_at = "" ;
    }


    public function mount($id){
    $this->trailer = Trailer::find($id);
    $this->documents = TrailerDocument::where('trailer_id', $this->trailer->id)->latest()->get();
    }

    public function store(){
        try{

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
                  $document->trailer_id = $this->trailer->id;
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

        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while uploading document(s)!!"
            ]);
        }
    }
    public function edit($id){

        $document = TrailerDocument::find($id);
        $this->user_id = $document->user_id;
        $this->trailer_id = $document->trailer_id;
        $this->title = $document->title;
        $this->expires_at = $document->expires_at;
        $this->filename = $document->filename;
        $this->document_id = $document->id;

        $this->dispatchBrowserEvent('show-trailerDocumentEditModal');

        }

        public function update()
        {
            if ($this->document_id) {
                try{
                
            if (isset($this->file)) {
              
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
                  $document =  TrailerDocument::find($this->document_id);
                  $document->trailer_id = $this->trailer->id;
                  if(isset($this->title)){
                  $document->title = $this->title;
                  }
                  if (isset($fileNameToStore)) {
                      $document->filename = $fileNameToStore;
                  }
                  if(isset($this->expires_at)){
                      $document->expires_at = Carbon::create($this->expires_at)->toDateTimeString();
                      $today = now()->toDateTimeString();
                      $expire = Carbon::create($this->expires_at)->toDateTimeString();
                      if ($today <=  $expire) {
                          $document->status = 1;
                      }else{
                          $document->status = 0;
                      }
                  }else {
                    $document->status = 1;
                  }
                  $document->update();
    
            
                       # code...
              }

                $this->dispatchBrowserEvent('hide-trailerDocumentEditModal');
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
        $this->documents = TrailerDocument::where('trailer_id', $this->trailer->id)->latest()->get();
        return view('livewire.trailers.documents',[
            'documents'=> $this->documents
        ]);
    }
}
