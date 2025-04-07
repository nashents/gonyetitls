<?php

namespace App\Http\Livewire\Documents;

use Carbon\Carbon;
use App\Models\Folder;
use Livewire\Component;
use App\Models\Document;
use App\Models\Department;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class All extends Component
{
    use WithFileUploads;

    public $category;
    public $department;
    public $department_id;
    public $item_id;
    public $user_id;
    public $document;
    public $documents;
    public $document_id;
    public $folder;
    public $folders;
    public $folder_id;
    public $selectedFolder;
    public $is_open = FALSE;
    public $folder_title;

    public $title;
    public $expires_at;
    public $file;
    public $filename;
    public $rand;
   


    private function resetInputFields(){
        $this->title = "";
        $this->document_id = "";
        $this->folder_title = "";
        $this->folder_id = "";
        $this->selectedFolder = "";
        $this->file = null;
        $this->rand++;
       
        $this->expires_at = "";
    }

    

    public function setFolder($id){
        if ($id == $this->selectedFolder ) {
            if ($this->is_open == FALSE) {
                $this->selectedFolder = $id;
                $this->is_open = TRUE;
            }else{
                $this->selectedFolder = NULL;
                $this->is_open = FALSE;
            }
        }else{
            $this->selectedFolder = $id;
            $this->is_open = TRUE;
        }
        
       
    }


    public function mount($id,$category){
        $this->category = $category;
        $this->item_id = $id;

    if ($this->category == "department") {
        $this->department = Department::find($id);
        $this->folders = Folder::where('category', $this->category)->orderBy('title','asc')->get();
        $this->documents = Document::where('category', $this->category)
        ->where('department_id', $this->department->id)->latest()->get();
    }


    }

    public function updatedSelectedFolder($selected_folder_id){

        if(!is_null($selected_folder_id)){

            if ($this->category == "department") {
                $this->department = Department::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('department_id', $this->department->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }

        }
    }

    public function showFolder(){
        $this->dispatchBrowserEvent('show-folderModal');
    }

    public function storeFolder(){
        try{

            $folder = new Folder;
            $folder->user_id = Auth::user()->id;
            $folder->category = $this->category;
            $folder->title = $this->folder_title;
            $folder->save();

            $this->folder_id = $folder->id;

            $this->dispatchBrowserEvent('hide-folderModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Folder Created Successfully!!"
            ]);

        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while creating folder!!"
            ]);
        }
    }
   

    public function showDocumentDelete($id){
        $this->document_id = $id;
        $this->document = Document::find($id);
        $this->dispatchBrowserEvent('show-documentDeleteModal');
    }
    public function deleteDocument(){
    
        $this->document->delete();
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Document Deleted Successfully Successfully!!"
        ]);
        $this->dispatchBrowserEvent('hide-documentDeleteModal');
    }

    public function showFolderDelete($id){
        $this->folder_id = $id;
        $this->folder = Folder::find($id);
        $this->dispatchBrowserEvent('show-folderDeleteModal');
    }
    public function deleteFolder(){
        $documents = $this->folder->documents;
        if (isset($documents)) {
            foreach ($documents as $document) {
                $document->delete();
            }
        }
        $this->folder->delete();
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Folder Deleted Successfully Successfully!!"
        ]);
        $this->dispatchBrowserEvent('hide-folderDeleteModal');
    }

    public function updateFolder(){
        try{

            $folder = Folder::find($this->folder_id);
            $folder->user_id = Auth::user()->id;
            $folder->category = $this->category;
            $folder->title = $this->folder_title;
            $folder->update();

            $this->dispatchBrowserEvent('hide-folderEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Folder Updated Successfully!!"
            ]);

        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while updating folder!!"
            ]);
        }
    }
    public function store(){
        // try{

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
            $document = new Document;


            if (isset($this->department)) {
                $document->department_id = $this->department->id;
            }


            $document->title = $this->title;

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
            $document->category = $this->category;
            $document->folder_id = $this->folder_id;
            $document->user_id = Auth::user()->id;
            $document->save();

         

            $this->dispatchBrowserEvent('hide-documentModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Document(s) Uploaded Successfully!!"
            ]);

            return redirect(request()->header('Referer'));

        // }catch(\Exception $e){
        //     // Set Flash Message
        //     $this->dispatchBrowserEvent('alert',[
        //         'type'=>'error',
        //         'message'=>"Something went wrong while uploading document(s)!!"
        //     ]);
        // }
    }

    public function editFolder($id){
        $folder = Folder::find($id);
        $this->category = $folder->category;
        $this->folder_title = $folder->title;
        $this->folder_id = $folder->id;

        $this->dispatchBrowserEvent('show-folderEditModal');

        }

    public function edit($id){

        $document = Document::find($id);
        $this->user_id = $document->user_id;
        $this->department_id = $document->department_id;
        $this->title = $document->title;
        $this->expires_at = $document->expiry_date;
        $this->filename = $document->filename;
        $this->document_id = $document->id;

        $this->dispatchBrowserEvent('show-documentEditModal');

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
                $document = Document::find($this->document_id);
                $document->title = $this->title;
                $document->folder_id = $this->folder_id;
                if (isset($fileNameToStore)) {
                    $document->filename = $fileNameToStore;
                }

                if (isset($this->department_id)) {
                    $document->department_id = $this->department_id;
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
                }
                $document->update();

                $this->dispatchBrowserEvent('hide-documentEditModal');
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
        if ($this->category == "department") {
            $this->folders = Folder::where('category', $this->category)->orderBy('title','asc')->get();
            $this->documents = Document::where('category', $this->category)
            ->where('department_id', $this->department->id)->latest()->get();
        }

 
 
     
        return view('livewire.documents.all',[
            'documents'=> $this->documents,
            'folders'=> $this->folders
        ]);
    }
}
