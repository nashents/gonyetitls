<?php

namespace App\Http\Livewire\Cashflows;

use Livewire\Component;
use App\Models\CashFlow;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    use WithFileUploads;
    public $cashflow;
    public $cashflow_id;
    public $horse_id;
    public $trip_id;
    public $fuel_id;
    public $currency_id;
    public $type;
    public $category;
    public $date;
    public $amount;
    public $comments;
    public $invoice;

    public function mount($cashflow){
        $this->cashflow = $cashflow;
        $this->cashflow_id = $cashflow->id;
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'invoice' => 'required|file',
    ];

    private function resetInputFields(){
        $this->invoice = '';
    }
    public function update($id){
        $cashflow = CashFlow::find($id);
        $this->cashflow_id = $cashflow->id;
        $this->dispatchBrowserEvent('show-receiptUploadModal');

        }
        public function uploadInvoice(){
            if($this->invoice){
                $file = $this->invoice;
                // get file with ext
                $fileNameWithExt = $file->getClientOriginalName();
                //get filename
                $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                //get extention
                $extention = $file->getClientOriginalExtension();
                //file name to store
                $fileNameToStore = $filename.'_'.time().'.'.$extention;
                $file->storeAs('/documents', $fileNameToStore, 'my_files');

            }
        $cashflow = CashFlow::find( $this->cashflow_id);
        $cashflow->user_id = Auth::user()->id;
        if (isset($fileNameToStore)) {
            $cashflow->invoice = $fileNameToStore;
        }
        $cashflow->update();
        $this->dispatchBrowserEvent('hide-receiptUploadModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Invoice Uploaded Successfully!!"
        ]);
        }
    public function render()
    {
        $this->cashflow = CashFlow::find($this->cashflow_id);
        return view('livewire.cashflows.show',[
            'cashflow' => $this->cashflow
        ]);
    }
}
