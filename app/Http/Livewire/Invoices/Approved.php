<?php

namespace App\Http\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\User;
use Livewire\Component;
use App\Models\TransportOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Approved extends Component
{
    public $invoices;
    public $invoice_id;
    public $trip_id;
    public $authorize;
    public $comments;

    public $date;
    public $fullname;
    public $supplier;
    public $email;
    public $regnumber;
    public $authorized_by;
    public $checked_by;
    public $invoice_type;
    public $quantity;
    public $driver;
    public $collection_point;
    public $delivery_point;
    public $trip;
    public $invoice;

    public function mount(){
        $period = Auth::user()->employee->company->period;
        if (isset( $period)) {
            if ($period != "all") {
                $this->invoices = invoice::where('authorization', 'approved')->whereYear('created_at',$period)->latest()->get();
            }else {
                $this->invoices = invoice::where('authorization', 'approved')->latest()->get();
            }
        }
    }
    public function authorize($id){
        $invoice = invoice::find($id);
        $this->invoice_id = $invoice->id;
        $this->invoice = $invoice;
        $this->trip_id = $invoice->trip_id;
        $this->trip = $invoice->trip;
        $this->dispatchBrowserEvent('show-invoiceAuthorizationModal');
      }

      public function update(){
          try{
            if ($this->trip->authorization == "approved") {
        $invoice = invoice::find($this->invoice_id);
        $invoice->authorized_by_id = Auth::user()->id;
        $invoice->authorization = $this->authorize;
        $invoice->comments = $this->comments;
        $invoice->update();



        if ($this->authorize == "approved") {
            if ($invoice->authorization =! "approved") {
            $container = Container::find($invoice->container_id);
            $container->balance = $container->balance - $invoice->balance;
            $container->update();

            $cashflow = new CashFlow;
            $cashflow->trip_id = $trip->id;
            $cashflow->user_id = Auth::user()->id;
            $cashflow->horse_id = $invoice->horse_id;
            $cashflow->invoice_id = $invoice->id;
            $cashflow->type = 'Expense';
            $cashflow->category = 'invoice';
            $cashflow->date = $invoice->date;
            $cashflow->amount = $invoice->amount;
            $cashflow->comments = $invoice->comments;
            $cashflow->save();

            if ($invoice->type == "Trip") {
            $trip = $invoice->trip;
            $user = User::find($trip->user_id);
              // sending Invoice email to supplier
            $this->email = $invoice->container->email;
            $this->date = $invoice->date;
            $this->supplier = $invoice->container->name;
            $this->driver = $invoice->driver->employee->name .' '. $invoice->driver->employee->surname;
            $this->collection_point = $invoice->trip->loading_point->name;
            $this->delivery_point = $invoice->trip->offloading_point->name;
            $this->invoice_type = $invoice->container->invoice_type;
            $this->quantity = $invoice->quantity;
            $this->checked_by = $invoice->user->employee->name . ' ' . $invoice->user->employee->surname;
            $this->authorized_by = Auth::user()->employee->name . ' ' . Auth::user()->employee->surname;
            $this->regnumber = $invoice->horse ? $invoice->horse->registration_number : "";

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
                'invoice_type'=> $invoice_type,
                'quantity'=> $quantity,
                'from'=> 'no-reply@tinmac.com',
                'subject'=> 'Auto generated Invoice confirmation'

               );
             Mail::send('emails.invoice_order',$data, function($message) use($data){
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
        //         return redirect()->route('invoices.approved');
        //     }else {
        //         return redirect()->route('invoices.rejected');
        //     }

        //    }
        $this->dispatchBrowserEvent('hide-invoiceAuthorizationModal');
        $this->dispatchBrowserEvent('alert',[
         'type'=>'success',
         'message'=>"Invoice Approved Successfully"
          ]);
         return redirect()->route('invoices.approved');
        }

        $this->dispatchBrowserEvent('hide-invoiceAuthorizationModal');
        $this->dispatchBrowserEvent('alert',[
         'type'=>'success',
         'message'=>"Invoice Approved Successfully"
          ]);
         return redirect()->route('invoices.approved');

        }else {
            $this->dispatchBrowserEvent('hide-invoiceAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Invoice Approved Already"
                 ]);
                 return redirect()->route('invoices.approved');
        }
        }else {
            $this->dispatchBrowserEvent('hide-invoiceAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Invoice Rejected Successfully"
                 ]);
                 return redirect()->route('invoices.rejected');
        }

    }else {
        $this->dispatchBrowserEvent('hide-invoiceAuthorizationModal');
        $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Trip needs to be authorized first!!"
            ]);
}

}
catch(\Exception $e){
    $this->dispatchBrowserEvent('hide-invoiceEditModal');
    $this->dispatchBrowserEvent('alert',[
        'type'=>'error',
        'message'=>"Something went wrong while trying to send email!!"
    ]);
    }

      }
    public function render()
    {
        $this->invoices = Invoice::where('authorization', 'approved')->latest()->get();
        return view('livewire.invoices.approved',[
            'invoices' => $this->invoices
        ]);
    }
}
