<?php

namespace App\Http\Livewire\AssetDocuments;

use App\Models\Vendor;
use Livewire\Component;
use App\Models\asset;
use App\Models\VendorType;
use Livewire\WithFileUploads;
use App\Models\AssetDocument;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithFileUploads;


    public $title;
    public $file;
    public $vendor_id;
    public $vendors;
    public $vendor_types;
    public $selectedVendorType;
    public $previous_file;
    public $asset_id;
    public $asset_documents;
    public $asset_document_id;

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

    public function mount($id){
        $this->asset = Asset::find($id);
        $this->vendor_types = VendorType::latest()->get();
        $this->vendors = Vendor::latest()->get();
        $this->asset_documents = AssetDocument::where('asset_id', $this->asset->id)->latest()->get();
        $this->asset_id = $this->asset->id;
    }

    public function updatedSelectedVendorType($vendor_type){
        if (!is_null($vendor_type)) {
            $this->vendors = Vendor::where('vendor_type_id', $vendor_type)->get();
        }
    }
    public function save(){
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
            $document = new AssetDocument;
            $document->user_id = Auth::user()->id;
            $document->asset_id = $this->asset_id;
            $document->vendor_id = $this->vendor_id[$key];
            $document->title = $this->title[$key];
            if (isset($fileNameToStore)) {
                $document->filename = $fileNameToStore;
            }
            $document->save();

                }
            }

            $this->dispatchBrowserEvent('hide-asset_documentsModal');
            // $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Document(s) Uploaded Successfully!!"
            ]);

        // }catch(\Exception $e){
        //     // Set Flash Message
        //     $this->dispatchBrowserEvent('alert',[
        //         'type'=>'error',
        //         'message'=>"Something went wrong while uploading quotation(s)!!"
        //     ]);
        // }
    }

    public function edit($id){

        $document = AssetDocument::find($id);
        $this->user_id = $document->user_id;
        $this->asset_id = $document->asset_id;
        $this->title = $document->title;
        $this->vendor_id = $document->vendor_id;
        $this->previous_file = $document->filename;
        $this->document_id = $document->id;

        $this->dispatchBrowserEvent('show-asset_documentsEditModal');

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
                $document = AssetDocument::find($this->document_id);
                $document->title = $this->title;
                if (isset($fileNameToStore)) {
                    $document->filename = $fileNameToStore;
                }
                $document->vendor_id = $this->vendor_id;
                $document->asset_id = $this->asset_id;

                $document->update();

                $this->dispatchBrowserEvent('hide-asset_documentsEditModal');
                // $this->resetInputFields();
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
        $this->asset_documents = AssetDocument::where('asset_id', $this->asset->id)->latest()->get();
        return view('livewire.asset-documents.index',[
            'asset_documents' => $this->asset_documents
        ]);
    }
}
