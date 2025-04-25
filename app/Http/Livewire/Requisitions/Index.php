<?php

namespace App\Http\Livewire\Requisitions;

use App\Models\Trip;
use App\Models\Account;
use App\Models\Booking;
use App\Models\Expense;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Requisition;
use App\Models\Notification;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Models\RequisitionItem;
use App\Exports\RequisitionExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\PendingNotificationEmails;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;

    public $requisition_filter;

    public $accounts;
    public $account;
    public $selectedAccount;
    public $requisition_for;
    public $trips;
    public $trip_id;
    public $bookings;
    public $booking_id;
    public $expenses;
    public $expense_id;
    public $currencies;
    public $currency_id;
    private $requisitions;
    public $requisition;
    public $requisition_number;
    public $subject;
    public $requisition_id;
    public $employees;
    public $employee;
    public $employee_id;
    public $departments;
    public $department;
    public $department_id;
    public $description;
    public $department_ids;
    public $date;
    public $qty;
    public $amount;
    public $total;
    public $subtotal;
    public $exchange_amount;
    public $selected_currency;
    public $company;
    public $exchange_rate;
    public $item_totals = 0;


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
        $this->requisition = Null;
        $this->trip_id = '';
        $this->employee_id = '';
        $this->department_id = '';
        $this->date = '';
        $this->currency_id = '';
        $this->expense_id = '';
        $this->selectedAccount = '';
        $this->qty = '';
        $this->amount = '';
        $this->description = '';
        $this->subject = '';
        $this->total = Null;
        $this->subtotal = Null;
    }
    

    public function exportRequisitionCSV(Excel $excel){
        return $excel->download(new RequisitionExport($this->from, $this->to, $this->requisition_filter,   $this->search), 'requisitions_' .time().'.csv', Excel::CSV);
    }
    public function exportRequisitionPDF(Excel $excel){
        return $excel->download(new RequisitionExport($this->from, $this->to, $this->requisition_filter,  $this->search), 'requisitions_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportRequisitionExcel(Excel $excel){
        return $excel->download(new RequisitionExport($this->from, $this->to, $this->requisition_filter,  $this->search), 'requisitions_' .time().'.xlsx');
    }

    public function calculateTotals(){
        $requisitions = Requisition::all();
        foreach($requisitions as $requisition){
           $items_total = $requisition->requisition_items->where('subtotal','!=',"")->where('subtotal','!=',Null)->sum('subtotal');
           $requisition->total = $items_total;
           $requisition->update();
        }

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"All Totals Updated Successfully!!"
        ]);
    }

    public function updatedCurrencyId($id){
        if (!is_null($id)) {
            $this->selected_currency = Currency::find($id);
        }
    }


    public function mount(){
        $this->resetPage();
        $departments = Auth::user()->employee->departments;
        foreach ($departments as $department) {
            $this->department_ids[] = $department->id;
        }
        $this->company = Auth::user()->employee->company;
        $this->requisition_filter = "created_at";
       
        $this->employees = Employee::orderBy('surname','asc')->get()->sortBy('name');
      
        $this->bookings = Booking::orderBy('created_at','desc')->where('authorization','approved')->where('status',True)->get();
        $this->departments = Department::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->expenses = Expense::orderBy('name','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
        'currency_id' => 'required',
        'expense_id.0' => 'required',
        'selectedAccount.0' => 'required',
        'employee_id.0' => 'required',
        'qty.0' => 'required',
        'amount.0' => 'required',
        'expense_id.*' => 'required',
        'selectedAccount.*' => 'required',
        'qty.*' => 'required',
        'amount.*' => 'required',
    ];


    public function requisitionNumber(){

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

        $requisition = Requisition::latest()->orderBy('id','desc')->first();

        if (!$requisition) {
            $requisition_number =  $initials .'RQ'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $requisition->id + 1;
            $requisition_number =  $initials .'RQ'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $requisition_number;


    }

    

    public function store(){

        // try{

        $requisition = new Requisition;
        $requisition->requisition_number = $this->requisitionNumber();
        $requisition->user_id = Auth::user()->id;
        $requisition->department_id = $this->department_id;
        $requisition->trip_id = $this->trip_id ? $this->trip_id : Null;
        $requisition->booking_id = $this->booking_id ? $this->booking_id : Null;
        $requisition->employee_id = $this->employee_id;
        $requisition->currency_id = $this->currency_id;
        $requisition->date = $this->date;
        $requisition->description = $this->description;
        $requisition->subject = $this->subject;
        $requisition->status = "Unpaid";
        $requisition->save();
        

        if (isset($this->expense_id)) {
            foreach($this->expense_id as $key => $value){
                $requisition_item = new RequisitionItem;
                $requisition_item->requisition_id = $requisition->id;
                if (isset($this->expense_id[$key])) {
                    $requisition_item->expense_id = $this->expense_id[$key];
                }
                if (isset($this->qty[$key])) {
                    $requisition_item->qty = $this->qty[$key];
                }
                if (isset($this->amount[$key])) {
                    $requisition_item->amount = $this->amount[$key];
                }
                if (isset($this->amount[$key]) && isset($this->qty[$key])) {
                $this->subtotal = ($this->amount[$key] * $this->qty[$key]);
                }
                $requisition_item->subtotal = $this->subtotal;
                $requisition_item->save();
                $this->total = $this->total +   $this->subtotal ;
            }
        }
        $requisition = Requisition::find($requisition->id);
        $requisition->total = $this->total;
        $requisition->exchange_rate = $this->exchange_rate;
        if (isset($this->exchange_rate) && isset($this->total)) {
           $exchange_amount = $this->exchange_rate * $this->total;
           $requisition->exchange_amount = $exchange_amount;
        }
        $requisition->update();

        $notifications = Notification::where('category','Requisition Authorization')->where('status',1)->get();
        $company = Auth::user()->employee->company;
        
        if (isset($notifications)) {
            foreach ($notifications as $notification) {
                if (isset($notification->email)) {   
                    Mail::to($notification->email)->send(new PendingNotificationEmails($company, $notification, $requisition));
                }elseif($notification->employee){
                    Mail::to($notification->employee->email)->send(new PendingNotificationEmails($company, $notification, $requisition));
                }
               
            }
        }

        $this->dispatchBrowserEvent('hide-requisitionModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Requisition Created Successfully!!"
        ]);

//     }
//     catch(\Exception $e){
//     // Set Flash Message
//     $this->dispatchBrowserEvent('alert',[
//         'type'=>'error',
//         'message'=>"Something goes wrong while creating requisition!!"
//     ]);
// }

    }
    
    public function showPayment($id){
        $requisition = Requisition::find($id);
        $this->requisition_id = $id;
        $this->requisition_number = $requisition->requisition_number;
        $this->dispatchBrowserEvent('show-requisitionPaymentModal');
    }

    public function recordPayment(){
        $requisition =  Requisition::find($this->requisition_id);
        $requisition->status = "Paid";
        $requisition->update();
        $this->dispatchBrowserEvent('hide-requisitionPaymentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Requisition Marked As Paid Successfully!!"
        ]);
    }

    public function edit($id){
        $requisition = Requisition::find($id);
        $this->currency_id = $requisition->currency_id;
        $this->trip_id = $requisition->trip_id;
        $this->booking_id = $requisition->booking_id;
        if (isset($this->trip_id)) {
           $this->requisition_for = "Trip";
        }elseif(isset($this->booking_id)){
            $this->requisition_for = "Booking";
        }
        $this->employee_id = $requisition->employee_id;
        $this->department_id = $requisition->department_id;
        $this->date = $requisition->date;
        $this->description = $requisition->description;
        $this->subject = $requisition->subject;
        $this->requisition_id = $requisition->id;
        $this->dispatchBrowserEvent('show-requisitionEditModal');
    }

        public function update(){

        // try{

        $requisition =  Requisition::find($this->requisition_id);
        $requisition->user_id = Auth::user()->id;
        $requisition->department_id = $this->department_id;
        $requisition->trip_id = $this->trip_id ? $this->trip_id : Null;
        $requisition->booking_id = $this->booking_id ? $this->booking_id : Null;
        $requisition->employee_id = $this->employee_id;
        $requisition->currency_id = $this->currency_id;
        $requisition->date = $this->date;
        $requisition->description = $this->description;
        $requisition->subject = $this->subject;
        $requisition->update();


        $this->dispatchBrowserEvent('hide-requisitionEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Requisition Updated Successfully!!"
        ]);

//     }
//     catch(\Exception $e){
//     // Set Flash Message
//     $this->dispatchBrowserEvent('alert',[
//         'type'=>'error',
//         'message'=>"Something goes wrong while creating requisition!!"
//     ]);
// }

    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        
        $user = Auth::user();
        $employee = $user->employee;
        $this->employees = Employee::orderBy('surname','asc')->get()->sortBy('name');
        $this->departments = Department::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->expenses = Expense::orderBy('name','asc')->get();
        $this->trips = Trip::orderBy('created_at','desc')->where('authorization','approved')->where('trip_status','!=','cancelled')->get();
        $departments = $employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = $user->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = $employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Finance', $department_names) || in_array('Super Admin', $role_names)){
            if (isset($this->from) && isset($this->to)) {
                if (filled($this->search)) {
                    return view('livewire.requisitions.index',[
                        'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')->whereBetween($this->requisition_filter,[$this->from, $this->to] )
                        ->where('requisition_number','like', '%'.$this->search.'%')
                        ->orWhere('subject','like', '%'.$this->search.'%')
                        ->orWhere('description','like', '%'.$this->search.'%')
                        ->orWhere('status','like', '%'.$this->search.'%')
                        ->orWhere('date','like', '%'.$this->search.'%')
                        ->orWhere('total','like', '%'.$this->search.'%')
                        ->orWhereHas('requisition_items', function ($query) {
                            $query->whereHas('expense', function ($q) {
                                $q->where('name', 'like', '%' . $this->search . '%');
                            });
                        })
                        ->orWhereHas('trip', function ($query) {
                            $query->where('trip_number', 'like', '%' . $this->search . '%')
                                  ->orWhereHas('horse', function ($q) {
                                      $q->where('registration_number', 'like', '%' . $this->search . '%');
                                  });
                        })
                        ->orWhereHas('employee', function ($query) {
                            return $query->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->search."%");
                        })
                        ->orWhereHas('currency', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orderBy($this->requisition_filter,'desc')->paginate(10),
                        'employees' => $this->employees,
                        'departments' => $this->departments,
                        'currencies' => $this->currencies,
                        'trips' => $this->trips,
                    ]);
                }else {
                    return view('livewire.requisitions.index',[
                        'requisitions' => requisition::query()->with('employee','department','trip','currency','payments')->whereBetween($this->requisition_filter,[$this->from, $this->to] )->orderBy($this->requisition_filter,'desc')->paginate(10),
                        'employees' => $this->employees,
                        'departments' => $this->departments,
                        'currencies' => $this->currencies,
                        'trips' => $this->trips,
                    ]);
                }
               
            }
            elseif (filled($this->search)) {
               
                return view('livewire.requisitions.index',[
                    'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')->whereMonth($this->requisition_filter, date('m'))
                    ->whereYear($this->requisition_filter, date('Y'))
                    ->where('requisition_number','like', '%'.$this->search.'%')
                    ->orWhere('subject','like', '%'.$this->search.'%')
                    ->orWhere('description','like', '%'.$this->search.'%')
                    ->orWhere('status','like', '%'.$this->search.'%')
                    ->orWhere('date','like', '%'.$this->search.'%')
                    ->orWhere('total','like', '%'.$this->search.'%')
                    ->orWhereHas('requisition_items', function ($query) {
                        $query->whereHas('expense', function ($q) {
                            $q->where('name', 'like', '%' . $this->search . '%');
                        });
                    })
                    ->orWhereHas('trip', function ($query) {
                        $query->where('trip_number', 'like', '%' . $this->search . '%')
                              ->orWhereHas('horse', function ($q) {
                                  $q->where('registration_number', 'like', '%' . $this->search . '%');
                              });
                    })
                    ->orWhereHas('employee', function ($query) {
                        return $query->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->search."%");
                    })
                    ->orWhereHas('currency', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy($this->requisition_filter,'desc')->paginate(10),
                    'employees' => $this->employees,
                    'departments' => $this->departments,
                    'currencies' => $this->currencies,
                    'trips' => $this->trips,
                ]);
            }
            else {
               
                return view('livewire.requisitions.index',[
                    'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')->whereMonth($this->requisition_filter, date('m'))
                    ->whereYear($this->requisition_filter, date('Y'))->orderBy($this->requisition_filter,'desc')->paginate(10),
                    'employees' => $this->employees,
                    'departments' => $this->departments,
                    'currencies' => $this->currencies,
                    'trips' => $this->trips,
                ]);
              
            }
           
        }else{

            //not super admin
            if (isset($this->from) && isset($this->to)) {
                if (filled($this->search)) {
                    return view('livewire.requisitions.index',[
                        'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')->whereBetween($this->requisition_filter,[$this->from, $this->to] )
                        ->where('user_id',Auth::user()->id)
                        ->orWhereIn('department_id', $this->department_ids)
                        ->where('requisition_number','like', '%'.$this->search.'%')
                        ->orWhere('subject','like', '%'.$this->search.'%')
                        ->orWhere('description','like', '%'.$this->search.'%')
                        ->orWhere('status','like', '%'.$this->search.'%')
                        ->orWhere('date','like', '%'.$this->search.'%')
                        ->orWhere('total','like', '%'.$this->search.'%')
                        ->orWhereHas('requisition_items', function ($query) {
                            $query->whereHas('expense', function ($q) {
                                $q->where('name', 'like', '%' . $this->search . '%');
                            });
                        })
                        ->orWhereHas('trip', function ($query) {
                            $query->where('trip_number', 'like', '%' . $this->search . '%')
                                  ->orWhereHas('horse', function ($q) {
                                      $q->where('registration_number', 'like', '%' . $this->search . '%');
                                  });
                        })
                        ->orWhereHas('employee', function ($query) {
                            return $query->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->search."%");
                        })
                        ->orWhereHas('currency', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orderBy($this->requisition_filter,'desc')->paginate(10),
                        'employees' => $this->employees,
                        'departments' => $this->departments,
                        'currencies' => $this->currencies,
                        'trips' => $this->trips,
                    ]);
                }else {
                    return view('livewire.requisitions.index',[
                        'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')
                        ->where('user_id',Auth::user()->id)
                        ->orWhereIn('department_id', $this->department_ids)
                        ->whereBetween($this->requisition_filter,[$this->from, $this->to] )->orderBy($this->requisition_filter,'desc')->paginate(10),
                        'employees' => $this->employees,
                        'departments' => $this->departments,
                        'currencies' => $this->currencies,
                        'trips' => $this->trips,
                    ]);
                }
               
            }
            elseif (filled($this->search)) {
               
                return view('livewire.requisitions.index',[
                    'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')->whereMonth($this->requisition_filter, date('m'))
                    ->whereYear($this->requisition_filter, date('Y'))
                    ->where('user_id',Auth::user()->id)
                    ->orWhereIn('department_id', $this->department_ids)
                    ->where('requisition_number','like', '%'.$this->search.'%')
                    ->orWhere('subject','like', '%'.$this->search.'%')
                    ->orWhere('description','like', '%'.$this->search.'%')
                    ->orWhere('status','like', '%'.$this->search.'%')
                    ->orWhere('date','like', '%'.$this->search.'%')
                    ->orWhere('total','like', '%'.$this->search.'%')
                    ->orWhereHas('requisition_items', function ($query) {
                        $query->whereHas('expense', function ($q) {
                            $q->where('name', 'like', '%' . $this->search . '%');
                        });
                    })
                    ->orWhereHas('trip', function ($query) {
                        $query->where('trip_number', 'like', '%' . $this->search . '%')
                              ->orWhereHas('horse', function ($q) {
                                  $q->where('registration_number', 'like', '%' . $this->search . '%');
                              });
                    })
                    ->orWhereHas('employee', function ($query) {
                        return $query->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->search."%");
                    })
                    ->orWhereHas('currency', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy($this->requisition_filter,'desc')->paginate(10),
                    'employees' => $this->employees,
                    'departments' => $this->departments,
                    'currencies' => $this->currencies,
                    'trips' => $this->trips,
                ]);
            }
            else {
               
                return view('livewire.requisitions.index',[
                    'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')
                    ->where('user_id',Auth::user()->id)
                    ->orWhereIn('department_id', $this->department_ids)
                    ->whereMonth($this->requisition_filter, date('m'))
                    ->whereYear($this->requisition_filter, date('Y'))->orderBy($this->requisition_filter,'desc')->paginate(10),
                    'employees' => $this->employees,
                    'departments' => $this->departments,
                    'currencies' => $this->currencies,
                    'trips' => $this->trips,
                ]);
              
            }
           
        }
      
  
   

    }
}
