<?php

namespace App\Http\Livewire\WorkshopServices;

use App\Models\Bill;
use App\Models\Horse;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Trailer;
use Livewire\Component;
use App\Models\Currency;
use App\Models\RateCard;
use App\Models\BillExpense;
use App\Models\Transporter;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Models\WorkshopService;
use Illuminate\Support\Facades\Auth;
use App\Exports\WorkshopServicesExport;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];

    private $workshop_services;
    public $horses;
    public $horse_id;
    public $accounts;
    public $account_id;
    public $trailers;
    public $trailer_id;
    public $vendors;
    public $vendor_id;
    public $selectedTransporter;
    public $selectedLoadStatus;
    public $currencies;
    public $selectedCurrency;
    public $transporters;
    public $start_date;
    public $end_date;
    public $amount;
    public $balance;
    public $status;
    public $workshop_service_id;
    public $user_id;
    public $days;
    public $workshop_service;
    public $description;
  

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


  

    public function workshop_serviceNumber(){

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

        $workshop_service = WorkshopService::latest()->orderBy('id','desc')->first();

        if (!$workshop_service) {
            $workshop_service_number =  $initials .'WS'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $workshop_service->id + 1;
            $workshop_service_number =  $initials .'WS'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $workshop_service_number;


    }

    public function mount(){
        $this->resetPage();
        $this->days = 1;
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
        $this->horses = collect();
        $this->trailers = collect();
    }

    public function updatedSelectedTransporter($id){
        if (!is_null($id)) {
            $this->horses = Horse::where('transporter_id',$id)->orderBy('registration_number','asc')->get();
            $this->trailers = Trailer::where('transporter_id',$id)->orderBy('registration_number','asc')->get();
           
        }
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'horse_id' => 'required',
        'selectedTransporter' => 'required',
        'start_date' => 'required',
    
    ];

    public function billNumber(){

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

        $bill = Bill::latest()->orderBy('id','desc')->first();

        if (!$bill) {
            $bill_number =  $initials .'B'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $bill->id + 1;
            $bill_number =  $initials .'B'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $bill_number;


    }

    private function resetInputFields(){
        $this->horse_id = '';
        $this->trailer_id = '';
        $this->vendor_id = '';
        $this->account_id = '';
        $this->start_date = '';
        $this->end_date = '';
        $this->status = '';
        $this->selectedTransporter = "" ;
        $this->selectedCurrency = "" ;
        $this->amount = "" ;
        $this->description = "" ;
        $this->selectedLoadStatus = "" ;
        $this->days = "" ;
    }

    public function exportWorkshopServicesCSV(Excel $excel){

        return $excel->download(new WorkshopServicesExport, 'services.csv', Excel::CSV);
    }
    public function exportWorkshopServicesPDF(Excel $excel){

        return $excel->download(new WorkshopServicesExport, 'service.pdf', Excel::DOMPDF);
    }
    public function exportWorkshopServicesExcel(Excel $excel){
        return $excel->download(new WorkshopServicesExport, 'services.xlsx');
    }

    public function store(){
        // try{

        $workshop_service = new WorkshopService;
        $workshop_service->user_id = Auth::user()->id;
        $workshop_service->workshop_service_number = $this->workshop_serviceNumber();
        $workshop_service->transporter_id = $this->selectedTransporter;
        $workshop_service->account_id = $this->account_id;
        $workshop_service->vendor_id = $this->vendor_id;
        $workshop_service->horse_id = $this->horse_id;
        $workshop_service->trailer_id = $this->trailer_id;
        $workshop_service->currency_id = $this->selectedCurrency ? $this->selectedCurrency : Null;
        $workshop_service->start_date = $this->start_date;
        $workshop_service->end_date = $this->end_date;
        $workshop_service->days = $this->days;
        $workshop_service->amount = $this->amount;
        $workshop_service->load_status = $this->selectedLoadStatus;
        $workshop_service->balance = $this->amount;
        $workshop_service->description = $this->description;
        $workshop_service->status = "Unpaid";
        $workshop_service->save();

        $bill = new Bill;
        $bill->user_id = Auth::user()->id;
        $bill->bill_number = $this->billNumber();
        $bill->workshop_service_id = $workshop_service->id;
        $bill->category = "Workshop Service";
        $bill->bill_date =  $this->start_date;
        $bill->currency_id = $this->selectedCurrency;
        $bill->total =  $this->amount;
        $bill->balance =  $this->amount;

        $bill->authorized_by_id = Auth::user()->id;
        $bill->authorization = 'approved';
        $bill->save();

        $bill_account = new BillExpense;
        $bill_account->user_id = Auth::user()->id;
        $bill_account->bill_id = $bill->id;
        $bill_account->currency_id = $this->selectedCurrency;
        $bill_account->account_id = $this->account_id;
        $bill_account->qty = 1;
        $bill_account->amount = $this->amount;
        $bill_account->subtotal = $this->amount;
        $bill_account->save();

        $this->dispatchBrowserEvent('hide-workshop_serviceModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"WorkshopService Created Successfully!!"
        ]);

        // return redirect()->route('workshop_services.index');

    //     }
    //     catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something goes wrong while creating workshop_service!!"
    //     ]);
    // }
    }

    public function edit($id){
    $workshop_service = WorkshopService::find($id);
    $this->user_id = $workshop_service->user_id;
    $this->selectedTransporter = $workshop_service->transporter_id;
    $this->horse_id = $workshop_service->horse_id;
    $this->trailer_id = $workshop_service->trailer_id;
    $this->account_id = $workshop_service->account_id;
    $this->vendor_id = $workshop_service->vendor_id;
    $this->amount = $workshop_service->amount;
    $this->balance = $workshop_service->balance;
    $this->selectedLoadStatus = $workshop_service->load_status;
    $this->selectedCurrency = $workshop_service->currency_id;
    $this->end_date = $workshop_service->end_date;
    $this->days = $workshop_service->days;
    $this->start_date = $workshop_service->start_date;
    $this->status = $workshop_service->status;
    $this->description = $workshop_service->description;
    $this->workshop_service_id = $workshop_service->id;
    $this->horses = Horse::where('transporter_id', $this->selectedTransporter)->orderBy('registration_number','asc')->get();
    $this->trailers = Trailer::where('transporter_id', $this->selectedTransporter)->orderBy('registration_number','asc')->get();
    $this->dispatchBrowserEvent('show-workshop_serviceEditModal');

    }


    public function update()
    {
        if ($this->workshop_service_id) {
            try{
            $workshop_service = WorkshopService::find($this->workshop_service_id);
            $workshop_service->transporter_id = $this->selectedTransporter;
            $workshop_service->account_id = $this->account_id;
            $workshop_service->vendor_id = $this->vendor_id;
            $workshop_service->horse_id = $this->horse_id;
            $workshop_service->trailer_id = $this->trailer_id;
            $workshop_service->currency_id = $this->selectedCurrency ? $this->selectedCurrency : Null;
            $workshop_service->start_date = $this->start_date;
            $workshop_service->end_date = $this->end_date;
            $workshop_service->amount = $this->amount;
            $workshop_service->load_status = $this->selectedLoadStatus;
            $workshop_service->balance = $this->amount;
            $workshop_service->days = $this->days;
            $workshop_service->description = $this->description;
            $workshop_service->update();

            $bill = $workshop_service->bill;
            if (isset($bill)) {
                $bill->workshop_service_id = $workshop_service->id;
                $bill->category = "Workshop Service";
                $bill->bill_date =  $this->start_date;
                $bill->currency_id = $this->selectedCurrency;
                $bill->total =  $this->amount;
                $bill->balance =  $this->amount;
                $bill->authorized_by_id = Auth::user()->id;
                $bill->authorization = 'approved';
                $bill->update();
            }
        
    
            $bill_account = $bill->bill_expenses->first();
            if(isset($bill_account)){
                $bill_account->user_id = Auth::user()->id;
                $bill_account->bill_id = $bill->id;
                $bill_account->currency_id = $this->selectedCurrency;
                $bill_account->account_id = $this->account_id;
                $bill_account->qty = 1;
                $bill_account->amount = $this->amount;
                $bill_account->subtotal = $this->amount;
                $bill_account->save();
            }
           

            $this->dispatchBrowserEvent('hide-workshop_serviceEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Workshop Service Updated Successfully!!"
            ]);


            // return redirect()->route('workshop_services.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-workshop_serviceEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating workshop service!!"
            ]);
          }
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function render()
    {
        if (isset($this->selectedLoadStatus) && isset($this->selectedCurrency)) {
            $rate_card = RateCard::where('load_status',$this->selectedLoadStatus)->where('currency_id',$this->selectedCurrency)->first();
            if ((isset($this->days) && $this->days != "" && $this->days != Null && is_numeric($this->days)) && (isset($rate_card) && $rate_card->rate != "" && $rate_card->rate != Null && is_numeric($rate_card->rate) ) ) {
                $this->amount = $this->days * $rate_card->rate;
            }
         
        }
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();

        if (isset($this->search)) {
        return view('livewire.workshop-services.index',[
            'workshop_services'=> WorkshopService::query()->with('currency','transporter','vendor','horse','trailer','user')
            ->where('workshop_service_number','like', '%'.$this->search.'%')
            ->orWhere('start_date','like', '%'.$this->search.'%')
            ->orWhere('end_date','like', '%'.$this->search.'%')
            ->orWhere('days','like', '%'.$this->search.'%')
            ->orWhere('load_status','like', '%'.$this->search.'%')
            ->orWhere('amount','like', '%'.$this->search.'%')
            ->orWhereHas('transporter', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('vendor', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('currency', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('horse', function ($query) {
                return $query->where('registration_number', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('horse', function ($query) {
                return $query->where('fleet_number', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('trailer', function ($query) {
                return $query->where('registration_number', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('trailer', function ($query) {
                return $query->where('fleet_number', 'like', '%'.$this->search.'%');
            })
            ->orderBy('created_at','desc')->paginate(10),
            'accounts'=>   $this->accounts,
            'vendors'=>   $this->vendors,
        ]);
        }else{
            return view('livewire.workshop-services.index',[
                'workshop_services'=>   WorkshopService::query()->with('currency','transporter','vendor','horse','trailer','user')->orderBy('created_at','desc')->paginate(10),
                'accounts'=>   $this->accounts,
                'vendors'=>   $this->vendors,
            ]); 
        }
    }
}
