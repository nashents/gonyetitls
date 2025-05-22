<?php

namespace App\Http\Livewire\Bills;

use App\Models\Bill;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TransportOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Approved extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $bill_filter;
    private $bills;
    public $bill_id;
    public $trip_id;
    public $authorize;
    public $comments;
    public $bill;


    public function mount(){
        $this->resetPage();
        $this->bill_filter = "created_at";
    }
    
    public function authorize($id){
        $bill = bill::find($id);
        $this->bill_id = $bill->id;
        $this->bill = $bill;
        $this->trip_id = $bill->trip_id;
        $this->trip = $bill->trip;
        $this->dispatchBrowserEvent('show-billAuthorizationModal');
      }

      public function update(){
          try{
            if ($this->trip->authorization == "approved") {
        $bill = bill::find($this->bill_id);
        $bill->authorized_by_id = Auth::user()->id;
        $bill->authorization = $this->authorize;
        $bill->comments = $this->comments;
        $bill->update();



        if ($this->authorize == "approved") {
            if ($bill->authorization =! "approved") {
            $container = Container::find($bill->container_id);
            $container->balance = $container->balance - $bill->balance;
            $container->update();

            $cashflow = new CashFlow;
            $cashflow->trip_id = $trip->id;
            $cashflow->user_id = Auth::user()->id;
            $cashflow->horse_id = $bill->horse_id;
            $cashflow->bill_id = $bill->id;
            $cashflow->type = 'Expense';
            $cashflow->category = 'bill';
            $cashflow->date = $bill->date;
            $cashflow->amount = $bill->amount;
            $cashflow->comments = $bill->comments;
            $cashflow->save();

            if ($bill->type == "Trip") {
            $trip = $bill->trip;
            $user = User::find($trip->user_id);
              // sending bill email to supplier
            $this->email = $bill->container->email;
            $this->date = $bill->date;
            $this->supplier = $bill->container->name;
            $this->driver = $bill->driver->employee->name .' '. $bill->driver->employee->surname;
            $this->collection_point = $bill->trip->loading_point->name;
            $this->delivery_point = $bill->trip->offloading_point->name;
            $this->bill_type = $bill->container->bill_type;
            $this->quantity = $bill->quantity;
            $this->checked_by = $bill->user->employee->name . ' ' . $bill->user->employee->surname;
            $this->authorized_by = Auth::user()->employee->name . ' ' . Auth::user()->employee->surname;
            $this->regnumber = $bill->horse ? $bill->horse->registration_number : "";

            if ($this->email != "") {
            $data = array(
                'email'=> $this->email,
                'date'=> $this->date,
                'supplier'=> $this->supplier,
                'driver'=> $this->driver,
                'regnumber'=> $this->regnumber,
                'authorized_by'=> $this->authorized_by,
                'checked_by'=> $this->checked_by,
                'collection_point'=> $this->collection_point,
                'delivery_point'=> $this->delivery_point,
                'bill_type'=> $bill_type,
                'quantity'=> $quantity,
                'from'=> 'no-reply@tinmac.com',
                'subject'=> 'Auto generated bill confirmation'

               );
             Mail::send('emails.bill_order',$data, function($message) use($data){
                 $message->to($data['email']);
                 $message->from($data['from']);
                 $message->subject($data['subject']);
             });
            }
            if ($trip->trailers->count()>0) {
                foreach ($trip->trailers as $trailer) {
                    $trailer_regnumbers[] = $trailer->registration_number;
                }
                $regnumbers_string = implode(",",$trailer_regnumbers);
            }
        //    $trip = TransportOrder::whereIn('trip_id', [$trip->id])->get();
        //    if (!$trip) {
           $transport_order = new TransportOrder;
           $transport_order->user_id = Auth::user()->id;
           $transport_order->trip_id = $trip->id;
           if (isset(Auth::user()->company)) {
            $transport_order->transporter =  Auth::user()->company->name;
           }elseif (isset(Auth::user()->employee->company)) {
            $transport_order->transporter = Auth::user()->employee->company->name;
           }
           $transport_order->name = $trip->driver->employee->name .' '. $trip->driver->employee->surname;
           $transport_order->idnumber = $trip->driver->employee->idnumber;
           $transport_order->horse_regnumber = $trip->horse->registration_number;
           if (isset($regnumbers_string)) {
            $transport_order->trailer_regnumber = $regnumbers_string;
           }
           $transport_order->collection_point = $trip->loading_point->name;
           $transport_order->delivery_point = $trip->offloading_point->name;
           $transport_order->product = $trip->cargo->name;
           $transport_order->weight = $trip->weight;
           $transport_order->date = $trip->start_date;
           $transport_order->checked_by = $user->employee->name . ' ' .$user->employee->surname;
           $transport_order->authorised_by = Auth::user()->employee->name . ' ' .Auth::user()->employee->surname;
           $transport_order->save();
        // }
        //     else {
        //     Session::flash('error','Tranportation order already exist');
        //     $this->dispatchBrowserEvent('hide-authorizationModal');
        //     if ($this->authorize == 'approved') {
        //         return redirect()->route('bills.approved');
        //     }else {
        //         return redirect()->route('bills.rejected');
        //     }

        //    }
        $this->dispatchBrowserEvent('hide-billAuthorizationModal');
        $this->dispatchBrowserEvent('alert',[
         'type'=>'success',
         'message'=>"bill Approved Successfully"
          ]);
         return redirect()->route('bills.approved');
        }

        $this->dispatchBrowserEvent('hide-billAuthorizationModal');
        $this->dispatchBrowserEvent('alert',[
         'type'=>'success',
         'message'=>"bill Approved Successfully"
          ]);
         return redirect()->route('bills.approved');

        }else {
            $this->dispatchBrowserEvent('hide-billAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"bill Approved Already"
                 ]);
                 return redirect()->route('bills.approved');
        }
        }else {
            $this->dispatchBrowserEvent('hide-billAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"bill Rejected Successfully"
                 ]);
                 return redirect()->route('bills.rejected');
        }

    }else {
        $this->dispatchBrowserEvent('hide-billAuthorizationModal');
        $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Trip needs to be authorized first!!"
            ]);
}

}
catch(\Exception $e){
    $this->dispatchBrowserEvent('hide-billEditModal');
    $this->dispatchBrowserEvent('alert',[
        'type'=>'error',
        'message'=>"Something went wrong while trying to send email!!"
    ]);
    }

      }

         
    public function dateRange(){
 
        // $this->resetPage();
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
   
            if (isset($this->from) && isset($this->to)) {
                if (isset($this->search)) {
                    return view('livewire.bills.approved',[
                        'bills' => Bill::query()->with('invoice','transporter','container','top_up','trip','horse','driver','purchase','currency','payments')
                        ->whereDate($this->bill_filter, '>=', $this->from)
                        ->whereDate($this->bill_filter, '<=', $this->to)
                        ->where('authorization','approved')
                        ->where('to_be_paid', True)
                        ->where('bill_number','like', '%'.$this->search.'%')
                        ->orWhere('status','like', '%'.$this->search.'%')
                        ->orWhere('bill_date','like', '%'.$this->search.'%')
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('trip', function ($query) {
                            return $query->where('trip_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('ticket', function ($query) {
                            return $query->where('ticket_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('currency', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('invoice', function ($query) {
                            return $query->where('invoice_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('transporter', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('container', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('purchase', function ($query) {
                            return $query->where('purchase_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('vendor', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orderBy('bill_number','desc')->paginate(10),
                        'bill_filter' => $this->bill_filter,
    
                       
                    ]);
                }else {
                    return view('livewire.bills.approved',[
                        'bills' => Bill::query()->with('invoice','transporter','container','top_up','trip','horse','driver','purchase','currency','payments')->where('authorization','approved')
                        ->whereDate($this->bill_filter, '>=', $this->from)
                        ->whereDate($this->bill_filter, '<=', $this->to)
                          ->where('to_be_paid', True)
                        ->orderBy('bill_number','desc')->paginate(10),
                        'bill_filter' => $this->bill_filter,
    
                       
                    ]);
                }
               
            }
            elseif (isset($this->search)) {
               
                return view('livewire.bills.approved',[
                    'bills' => Bill::query()->with('invoice','transporter','container','top_up','trip','horse','driver','purchase','currency','payments')->whereMonth('created_at', date('m'))
                    ->where('authorization','approved')
                    ->whereYear('created_at', date('Y'))
                      ->where('to_be_paid', True)
                    ->where('bill_number','like', '%'.$this->search.'%')
                    ->orWhere('status','like', '%'.$this->search.'%')
                    ->orWhere('bill_date','like', '%'.$this->search.'%')
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('ticket', function ($query) {
                        return $query->where('ticket_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('trip', function ($query) {
                        return $query->where('trip_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('invoice', function ($query) {
                        return $query->where('invoice_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('transporter', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('currency', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('container', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('purchase', function ($query) {
                        return $query->where('purchase_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('vendor', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy('bill_number','desc')->paginate(10),
                    'bill_filter' => $this->bill_filter,

                   
                ]);
            }
            else {
               
                return view('livewire.bills.approved',[
                    'bills' => Bill::query()->with('invoice','transporter','container','top_up','trip','horse','driver','purchase','currency','payments')->whereMonth('created_at', date('m'))
                    ->where('authorization','approved')
                      ->where('to_be_paid', True)
                    ->whereYear($this->bill_filter, date('Y'))->orderBy('bill_number','desc')->paginate(10),
                    'bill_filter' => $this->bill_filter,

                   
                ]);
              
            }
        

    }
}
