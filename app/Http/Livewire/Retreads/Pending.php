<?php

namespace App\Http\Livewire\Retreads;

use App\Models\Bill;
use App\Models\Retread;
use Livewire\Component;
use App\Models\BillExpense;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Pending extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $retread_filter;

    private $retreads;
    public $retread_id;
    public $authorize;
    public $comments;
    public $retread;

    public function mount(){
        $this->retread_filter = 'created_at';
        $this->resetPage();
    }

    
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
    
    public function authorize($id){
        $retread = Retread::find($id);
        $this->retread_id = $retread->id;
        $this->retread = $retread;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

      public function update(){
    //   try{

            $retread = Retread::find($this->retread_id);
            $retread->authorized_by_id = Auth::user()->id;
            $retread->authorization = $this->authorize;
            $retread->reason = $this->comments;
            $retread->update();

        if ($this->authorize == "approved") {

            $bill = new Bill;
            $bill->user_id = Auth::user()->id;
            $bill->bill_number = $this->billNumber();
            $bill->retread_id = $this->retread->id;
            $bill->account_id = $this->retread->account_id;
            $bill->account_type_id = $this->retread->account_type_id;
            $bill->category = "Retread";
            $bill->bill_date = $this->retread->date;
            $bill->currency_id = $this->retread->currency_id;
            $bill->total = $this->retread->total;
            $bill->balance = $this->retread->total;

            $bill->authorized_by_id = Auth::user()->id;
            $bill->authorization = $this->authorize;
            $bill->comments = $this->comments;
            $bill->save();

            $retread_tyres = $retread->retread_tyres;
            if ($retread_tyres) {
                foreach ($retread_tyres as $retread_tyre) {
                    $bill_expense = new BillExpense;
                    $bill_expense->user_id = Auth::user()->id;
                    $bill_expense->bill_id = $bill->id;
                    $bill_expense->product_id = $retread_tyre->tyre->product_id;
                    $bill_expense->currency_id = $bill->currency_id;
                    $bill_expense->account_id = $this->retread->account_id;
                    $bill_expense->account_type_id = $this->retread->account_type_id;
                    $bill_expense->qty = 1;
                    $bill_expense->amount = $this->retread->total;
                    $bill_expense->subtotal = $this->retread->total;
                    $bill_expense->save();
                }
            }
           

            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Retread Approved Successfully"
            ]);
            return redirect()->route('retreads.approved');
        }else {
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Retread Rejected Successfully"
            ]);
            return redirect()->route('retreads.rejected');
        }
// }
// catch(\Exception $e){
//     $this->dispatchBrowserEvent('hide-authorizationModal');
//     $this->dispatchBrowserEvent('alert',[
//         'type'=>'error',
//         'message'=>"Something went wrong while trying to authorize retread!!"
//     ]);
//     }

      }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        if (isset($this->from) && isset($this->to)) {
            if (isset($this->search)) {
                return view('livewire.retreads.pending',[
                    'retreads' => Retread::query()->with('currency','vendor','account','retread_tyres')->where('authorization','pending')->whereBetween($this->retread_filter,[$this->from, $this->to] )
                    ->where('retread_number','like', '%'.$this->search.'%')
                    ->orWhere('status','like', '%'.$this->search.'%')
                    ->orWhere('date','like', '%'.$this->search.'%')
                    ->orWhereHas('currency', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy('retread_number','desc')->paginate(10),
                    'retread_filter' => $this->retread_filter,
                ]);
            }else {
                return view('livewire.retreads.pending',[
                    'retreads' => Retread::query()->with('currency','vendor','account','retread_tyres')->where('authorization','pending')->whereBetween($this->retread_filter,[$this->from, $this->to] )->orderBy('retread_number','desc')->paginate(10),
                    'retread_filter' => $this->retread_filter,
                ]);
            }
           
        }
        elseif (isset($this->search)) {
           
            return view('livewire.retreads.pending',[
                'retreads' => Retread::query()->with('currency','vendor','account','retread_tyres')->where('authorization','pending')->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->where('retread_number','like', '%'.$this->search.'%')
                ->orWhere('status','like', '%'.$this->search.'%')
                ->orWhere('date','like', '%'.$this->search.'%')
                ->orWhereHas('currency', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('retread_number','desc')->paginate(10),
                'retread_filter' => $this->retread_filter,
            ]);
        }
        else {
           
            return view('livewire.retreads.pending',[
                'retreads' => Retread::query()->with('currency','vendor','account','retread_tyres')->where('authorization','pending')->whereMonth('created_at', date('m'))
                ->whereYear($this->retread_filter, date('Y'))->orderBy('retread_number','desc')->paginate(10),
                'retread_filter' => $this->retread_filter,
            ]);
          
        }
    }
}
