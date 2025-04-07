<?php

namespace App\Http\Livewire\Horses;

use App\Models\Horse;
use Livewire\Component;
use App\Models\Document;
use App\Models\Employee;
use Livewire\WithFileUploads;
use App\Models\HorseDocument;
use Illuminate\Support\Facades\Auth;

class Documents extends Component
{
    use WithFileUploads;

    public $horse;
    public $horse_id;
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
    $this->horse = Horse::find($id);
    $this->documents = HorseDocument::where('horse_id', $this->horse->id)->latest()->get();
    }

    public function store(){
        try{

            if (isset($this->title)) {
                foreach ($this->title as $key => $value) {

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
            $document = new HorseDocument;
            $document->user_id = Auth::user()->id;
            $document->horse_id = $this->horse->id;
            $document->title = $this->title[$key];
            if (isset($fileNameToStore)) {
                $document->filename = $fileNameToStore;
            }
            if (isset($this->expires_at[$key])) {
                $document->expires_at = $this->expires_at[$key];
                $today = date("Y-m-d");
                $today_time = strtotime($today);
                $expire_time = strtotime($this->expires_at[$key]);
                if ($today_time <=  $expire_time) {
                    $document->status = 1;
                }else{
                    $document->status = 0;
                }

            }else{
                $document->status = 1;
            }
            $document->save();

                }
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

        $document = HorseDocument::find($id);
        $this->user_id = $document->user_id;
        $this->horse_id = $document->horse_id;
        $this->title = $document->title;
        $this->expires_at = $document->expires_at;
        $this->filename = $document->filename;
        $this->document_id = $document->id;

        $this->dispatchBrowserEvent('show-HorseDocumentEditModal');

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
                $document = HorseDocument::find($this->document_id);
                $document->title = $this->title;

                if (isset($fileNameToStore)) {
                    $document->filename = $fileNameToStore;
                }

                $document->horse_id = $this->horse_id;
                $document->user_id = Auth::user()->id;

                if ($this->expires_at == null) {
                    $document->expires_at = null;
                    $document->status = 1;
                }else{
                    $document->expires_at = $this->expires_at;
                    $today = date("Y-m-d");
                    $today_time = strtotime($today);
                    $expire_time = strtotime($this->expires_at);
                    if ($today_time <=  $expire_time) {
                        $document->status = 1;
                    }else{
                        $document->status = 0;
                    }


                }
                $document->update();

                $this->dispatchBrowserEvent('hide-HorseDocumentEditModal');
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
        $this->documents = HorseDocument::where('horse_id', $this->horse->id)->latest()->get();
        return view('livewire.horses.documents',[
            'documents'=> $this->documents
        ]);
    }
}
