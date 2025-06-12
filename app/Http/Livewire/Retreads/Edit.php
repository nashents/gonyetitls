<?php

namespace App\Http\Livewire\Retreads;

use App\Models\Tyre;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\Retread;
use Livewire\Component;
use App\Models\Currency;
use App\Models\RetreadTyre;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public $searchTyre;
    protected $queryString = ['searchTyre'];

    public $tyres;
    public $tyre_id = [];
    public $amount;
    public $trailers;
    public $trailer_id;
    public $horses;
    public $horse_id;
    public $vehicles;
    public $vehicle_id;
    public $vendors;
    public $vendor_id;
    public $currencies;
    public $currency_id;
    public $date;
    public $collection_date;
    public $total;
    public $description;
    public $accounts;
    public $account_id;

    public $title;
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

    public $documents_inputs = [];
    public $p = 1;


    public function documentsAdd($p)
    {
        $p = $p + 1;
        $this->p = $p;
        array_push($this->documents_inputs ,$p);
    }

    public function documentsRemove($p)
    {
        unset($this->documents_inputs[$p]);
    }



    public function mount($id){

        $this->retread_id = $id;
        $this->retread = Retread::find($id);

        $retread_tyres = $this->retread->retread_tyres;
        if (isset($retread_tyres)) {
            foreach ($retread_tyres as $retread_tyre) {
                $this->tyre_id[] = $retread_tyre->tyre->id;
            }
        }
        $this->date = $this->retread->date;
        $this->description = $this->retread->description;
        $this->collection_date = $this->retread->collection_date;
        $this->status = $this->retread->status;
        $this->authorization = $this->retread->authorization;
        $this->currency_id = $this->retread->currency_id;
        $this->amount = $this->retread->total;
        $this->vendor_id = $this->retread->vendor_id;
        $this->account_id = $this->retread->account_id;

        $this->accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
    
        $this->tyres = Tyre::with('product.brand')->get()->sortBy('product.brand.name');
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
       

    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages =[

      'tyre_id.*.required' => 'Tyre Dispatch field is required',
      'currency_id.required' => 'Select Currency',
      'vendor_id.required' => 'Select Vendor',

  ];
    protected $rules = [
        'vendor_id' => 'required',
        'currency_id' => 'required',
        'date' => 'required',
        'description' => 'required',
        'tyre_id.*' => 'required',
        'amount' => 'required',
    ];


    public function update(){

        $retread = Retread::find($this->retread_id);
        $retread->currency_id = $this->currency_id;
        $retread->total = $this->amount;
        $retread->vendor_id = $this->vendor_id;
        $retread->date = $this->date;
        $retread->account_id = $this->account_id;
        $retread->status = $this->status;
        $expense_account = Account::find($this->account_id);
        if (isset( $expense_account)) {
          $retread->account_type_id = $expense_account->account_type_id;
        }  
        $retread->collection_date = $this->collection_date;
        $retread->description = $this->description;
       
        $retread->update();

        $retread_tyres = $retread->retread_tyres;
        if (isset($retread_tyres)) {
            foreach ($retread_tyres as $retread_tyre) {
                $retread_tyre->delete();
            }
        }

        if (isset($this->tyre_id)) {

        foreach ($this->tyre_id as $key => $value) {
          
          $retread_tyre = new RetreadTyre;
          $retread_tyre->retread_id = $retread->id;
          if (isset($this->tyre_id[$key])) {
          $retread_tyre->tyre_id = $this->tyre_id[$key];
          }
        
          $retread_tyre->save();

          if (isset($this->tyre_id[$key])) {
              $tyre = Tyre::find($this->tyre_id[$key]);
              $tyre->update();

              $assignments = $tyre->tyre_assignments;
                if ($assignments) {
                    foreach ($assignments as $assignment) {
                        $assignment->status = 0;
                        $assignment->update();
                    }
                }
          }
        }
      }

      $this->dispatchBrowserEvent('alert',[
          'type'=>'success',
          'message'=>"Retread Updated Successfully!!"
      ]);
      return redirect()->route('retreads.index');
    }

    public function render()
    {
        
        if (isset($this->searchTyre)) {
            $this->tyres = Tyre::query()->with('product:id,name','product.brand:id,name')->where('disposed',0)
            ->where('tyre_number', 'like', '%'.$this->searchTyre.'%')
            ->orWhere('serial_number', 'like', '%'.$this->searchTyre.'%')
            ->orWhere('width', 'like', '%'.$this->searchTyre.'%')
            ->orWhere('diameter', 'like', '%'.$this->searchTyre.'%')
            ->orWhere('aspect_ratio', 'like', '%'.$this->searchTyre.'%')
            ->orWhere('type', 'like', '%'.$this->searchTyre.'%')
            ->orWhereHas('product', function ($query) {
                return $query->where('name', 'like', '%'.$this->searchTyre.'%');
            })
            ->orWhereHas('product.brand', function ($query) {
                return $query->where('name', 'like', '%'.$this->searchTyre.'%');
            })
            ->get();
            
        }

        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();

        return view('livewire.retreads.edit',[
            'tyres' => $this->tyres,
            'currencies' => $this->currencies,
            'vendors' => $this->vendors,
            
        ]);
    }
}
