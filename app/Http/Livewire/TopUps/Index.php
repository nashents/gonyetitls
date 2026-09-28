<?php

namespace App\Http\Livewire\TopUps;

use Carbon\Carbon;
use App\Models\TopUp;
use App\Services\Accounting\FuelJournalService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Container;
use Illuminate\Support\Str;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\PendingNotificationEmails;

class Index extends Component
{
    public $top_ups;
    public $order_number;
    public $top_up_id;
    public $currency_id;
    public $currencies;
    public $container_id;
    public $date;
    public $containers;
    public $quantity;
    public $rate;
    public $fuel_type;
    public $amount;
    public $balance;
    public $company;
    public $selected_top_up;



    public $user_id;

    public function mount(){
        $this->top_ups = TopUp::orderBy('created_at','desc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->containers = Container::orderBy('name','asc')->get();
        $this->company = Auth::user()->employee->company;
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [

        'currency_id' => 'required',
        'container_id' => 'required',
        'date' => 'required',
        'quantity' => 'required',
        'fuel_type' => 'required',
        'rate' => 'required',
    
        ];

    public function delete($id){
        $this->top_up_id = $id;
        $this->selected_top_up = TopUp::find($id);
        $this->dispatchBrowserEvent('show-deleteModal');
    }
   
    public function destroy(){

        $topup = TopUp::find($this->top_up_id);

        // Take its bill/customer supply out of the ledger with it - refused
        // (RuntimeException) if the supplier was already paid on the bill.
        try {
            DB::transaction(function () use ($topup) {
                app(FuelJournalService::class)->reverseTopUp($topup, "Top up {$topup->order_number} deleted");
                $topup->delete();
            });
        } catch (\RuntimeException $e) {
            $this->dispatchBrowserEvent('hide-deleteModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Top up not deleted: " . $e->getMessage()
            ]);
            return;
        }

        $this->dispatchBrowserEvent('hide-deleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Top Up Deleted Successfully!!"
        ]);

    }

    private function resetInputFields(){


        $this->container_id = "";
        $this->date = "";
        $this->currency_id = "";
        $this->fuel_type = "";
        $this->quantity = "";
        $this->rate = "";
        $this->amount = "";
    }

      public function top_upNumber(){

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

        $top_up = TopUp::latest()->orderBy('id','desc')->first();

        if (!$top_up) {
            $top_up_number =  $initials .'FT'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $top_up->id + 1;
            $top_up_number =  $initials .'FT'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $top_up_number;


    }

    public function store(){
        try{
        $top_up = new TopUp;
        $top_up->user_id = Auth::user()->id;
        $top_up->order_number = $this->top_upNumber();
        $top_up->container_id = $this->container_id;
        $top_up->date = $this->date;
        $top_up->currency_id = $this->currency_id;
        $top_up->fuel_type = $this->fuel_type;
        $top_up->quantity = $this->quantity;
        $top_up->rate = $this->rate;
        $top_up->amount = $this->amount;
        // Pending - the tank is topped up (by TopUpObserver) once it's
        // approved. This used to overwrite the tank balance with the top-up
        // quantity here, before approval.
        $top_up->save();

        $notifications = Notification::where('when','before')->where('category','Fuel Top Up Authorization')->where('status',1)->get();
                
        if ($notifications->isNotEmpty()) {
            foreach ($notifications as $notification) {
                if($notification && isset($notification->category)){
                $email = $notification->email ?? $notification->employee->email ?? null;
                if($email){
                    Mail::to($email)->send(new PendingNotificationEmails($this->company, $notification, $top_up));
                }
                }
            }
        }

        $this->dispatchBrowserEvent('hide-top_upModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Fuel Top Up Created Successfully!!"
        ]);
        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('hide-top_upModal');
        $this->dispatchBrowserEvent('alert',[

            'type'=>'error',
            'message'=>"Something went wrong while creating broker!!"
        ]);
    }

    }

    public function edit($id){
    $top_up = TopUp::find($id);
    $this->user_id = $top_up->user_id;
    $this->container_id = $top_up->container_id;
    $this->currency_id = $top_up->currency_id;
    $this->fuel_type = $top_up->fuel_type;
    $this->date = $top_up->date;
    $this->quantity = $top_up->quantity;
    $this->rate = $top_up->rate;
    $this->amount = $top_up->amount;
    $this->top_up_id = $top_up->id;
    $this->dispatchBrowserEvent('show-top_upEditModal');

    }


    public function update()
    {
        if ($this->top_up_id) {
            try{
            DB::transaction(function () {
                $top_up = TopUp::find($this->top_up_id);
                $top_up->update([
                    'user_id' => Auth::user()->id,
                    'container_id' => $this->container_id,
                    'currency_id' => $this->currency_id,
                    'fuel_type' => $this->fuel_type,
                    'date' => $this->date,
                    'quantity' => $this->quantity,
                    'rate' => $this->rate,
                    'amount' => $this->amount,
                ]);

                // Already in the ledger - repost its bill at the new figures
                // (a no-op if the amount/rate/currency didn't change).
                if ($top_up->authorization === 'approved') {
                    app(FuelJournalService::class)->postTopUp($top_up->fresh());
                }
            });
            $this->dispatchBrowserEvent('hide-top_upEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fuel Supplier Updated Successfully!!"
            ]);
            }
            catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('hide-top_upEditModal');
            $this->dispatchBrowserEvent('alert',[

                'type'=>'error',
                'message'=>"Top up not updated: " . $e->getMessage()
            ]);
        }
        }
    }

    public function render()
    {
        $this->top_ups = TopUp::orderBy('created_at','desc')->get();
        if ($this->quantity != null && $this->rate != null) {
            $this->amount = $this->quantity * $this->rate;
        }
        return view('livewire.top-ups.index',[
            'amount' => $this->amount,
            'top_ups' =>$this->top_ups
        ]);
    }
}
