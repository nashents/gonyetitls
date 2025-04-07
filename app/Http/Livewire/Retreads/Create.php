<?php

namespace App\Http\Livewire\Retreads;

use App\Models\Tyre;
use App\Models\Horse;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\Product;
use App\Models\Retread;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Purchase;
use App\Models\TyreCount;
use App\Models\TyreDetail;
use App\Models\RetreadTyre;
use App\Models\TyreDispatch;
use Livewire\WithFileUploads;
use App\Models\RetreadDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Create extends Component
{
    use WithFileUploads;

    public $searchTyre;
    protected $queryString = ['searchTyre'];

    public $tyres;
    public $tyre_id;
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

    public function mount(){
        $this->accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
    
        $this->retread_number = $this->retreadNumber();
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


      public function retreadNumber(){

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

            $retread = Retread::orderBy('id','desc')->first();

        if (!$retread) {
            $retread_number =  $initials .'R'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $retread->id + 1;
            $retread_number =  $initials .'R'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $retread_number;


    }


      public function store(){

          $retread = new Retread;
          $retread->user_id = Auth::user()->id;
          $retread->retread_number = $this->retreadNumber();
          $retread->currency_id = $this->currency_id;
          $retread->total = $this->amount;
          $retread->vendor_id = $this->vendor_id;
          $retread->date = $this->date;
          $retread->account_id = $this->account_id;
          $retread->status = 1;
          $expense_account = Account::find($this->account_id);
          if (isset( $expense_account)) {
            $retread->account_type_id = $expense_account->account_type_id;
          }  
          $retread->collection_date = $this->collection_date;
          $retread->description = $this->description;
          $retread->authorization = 'pending';
          $retread->save();

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
                $tyre->status = 0;
                $tyre->update();
            }
          }
        }

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Tyre(s) added for retreading successfully!!"
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

        return view('livewire.retreads.create',[
            'tyres' => $this->tyres,
            'currencies' => $this->currencies,
            'vendors' => $this->vendors,
            
        ]);
    }
}
