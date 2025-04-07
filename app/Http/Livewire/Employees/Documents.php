<?php

namespace App\Http\Livewire\Employees;

use Livewire\Component;
use App\Models\Document;
use App\Models\Employee;
use Livewire\WithFileUploads;

class Documents extends Component
{
    use WithFileUploads;

    public $employee;
    public $employee_id;
    public $documents;
    public $document_id;

    public $title;
    public $expiry_date;
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
    $this->employee = Employee::find($id);
    $this->documents = Document::where('employee_id', $this->employee->id)->latest()->get();
    }

    public function store(){
        // try{

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
            $document = new Document;
            $document->employee_id = $this->employee->id;
            $document->title = $this->title[$key];
            if (isset($fileNameToStore)) {
                $document->filename = $fileNameToStore;
            }
            if (isset($this->expiry_date[$key])) {
                $document->expiry_date = $this->expiry_date[$key];
                $today = date("Y-m-d");
                $today_time = strtotime($today);
                $expire_time = strtotime($this->expiry_date[$key]);
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

        // }catch(\Exception $e){
        //     // Set Flash Message
        //     $this->dispatchBrowserEvent('alert',[
        //         'type'=>'error',
        //         'message'=>"Something went wrong while uploading document(s)!!"
        //     ]);
        // }
    }

    public function edit($id){

        $document = Document::find($id);
        $this->user_id = $document->user_id;
        $this->employee_id = $document->employee_id;
        $this->title = $document->title;
        $this->expiry_date = $document->expiry_date;
        $this->filename = $document->filename;
        $this->document_id = $document->id;

        $this->dispatchBrowserEvent('show-documentEditModal');

        }

        public function update()
        {
            if ($this->document_id) {
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
                $document = Document::find($this->document_id);
                $document->title = $this->title;
                if (isset($fileNameToStore)) {
                    $document->filename = $fileNameToStore;
                }
                $document->employee_id = $this->employee_id;

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

                $this->dispatchBrowserEvent('hide-documentEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Document Updated Successfully!!"
                ]);
            // }catch(\Exception $e){
            //     // Set Flash Message
            //     $this->dispatchBrowserEvent('alert',[
            //         'type'=>'error',
            //         'message'=>"Something went wrong while updating document(s)!!"
            //     ]);
            // }

            }
        }


    public function render()
    {
        $this->documents = Document::where('employee_id', $this->employee->id)->latest()->get();
        return view('livewire.employees.documents',[
            'documents'=> $this->documents
        ]);
    }
}
