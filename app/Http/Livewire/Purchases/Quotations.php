<?php

namespace App\Http\Livewire\Purchases;

use App\Models\Vendor;
use Livewire\Component;
use App\Models\Purchase;
use App\Models\VendorType;
use Livewire\WithFileUploads;
use App\Models\PurchaseDocument;
use Illuminate\Support\Facades\Auth;

class Quotations extends Component
{
    use WithFileUploads;


    public $title;
    public $vendor_id;
    public $vendors;
    public $vendor_types;
    public $selectedVendorType;
    public $file;
    public $previous_file;
    public $purchase_id;
    public $purchase_documents;
    public $purchase_document_id;

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
        $this->purchase = Purchase::find($id);
        $this->vendor_types = VendorType::latest()->get();
        $this->vendors = Vendor::latest()->get();
        $this->purchase_documents = PurchaseDocument::where('purchase_id', $this->purchase->id)->latest()->get();
        $this->purchase_id = $this->purchase->id;
    }
    public function updatedSelectedVendorType($vendor_type){
        if (!is_null($vendor_type)) {
            $this->vendors = Vendor::where('vendor_type_id', $vendor_type)->get();
        }
    }
    public function save(){
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
            $document = new PurchaseDocument;
            $document->user_id = Auth::user()->id;
            $document->purchase_id = $this->purchase_id;
            $document->title = $this->title[$key];
            $document->vendor_id = $this->vendor_id[$key];
            if (isset($fileNameToStore)) {
                $document->filename = $fileNameToStore;
            }
            $document->save();

                }
            }

            $this->dispatchBrowserEvent('hide-purchaseQuotationsModal');
            // $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Document(s) Uploaded Successfully!!"
            ]);

        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while uploading quotation(s)!!"
            ]);
        }
    }

    public function edit($id){

        $document = PurchaseDocument::find($id);
        $this->user_id = $document->user_id;
        $this->purchase_id = $document->purchase_id;
        $this->title = $document->title;
        $this->vendor_id = $document->vendor_id;
        $this->previous_file = $document->filename;
        $this->document_id = $document->id;

        $this->dispatchBrowserEvent('show-purchaseQuotationsEditModal');

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
                $document = PurchaseDocument::find($this->document_id);
                $document->title = $this->title;
                $document->vendor_id = $this->vendor_id;
                if (isset($fileNameToStore)) {
                    $document->filename = $fileNameToStore;
                }
                $document->purchase_id = $this->purchase_id;

                $document->update();

                $this->dispatchBrowserEvent('hide-purchaseQuotationsEditModal');
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
        $this->purchase_documents = PurchaseDocument::where('purchase_id', $this->purchase->id)->latest()->get();
        return view('livewire.purchases.quotations',[
            'purchase_documents' => $this->purchase_documents
        ]);
    }
}
