<?php

namespace App\Http\Livewire\TicketRequests;


use App\Models\Product;
use Livewire\Component;
use App\Models\Measurement;
use App\Models\TicketRequest;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $search_products;
    protected $queryString = ['search_products'];
    public $ticket;
    public $ticket_id;
    public $measurements;
    public $measurement;
    public $products;
    public $selectedProduct;
    public $requests;
    public $ticket_requests;
    public $ticket_request_id;
    public $qty;
    public $tyres;
    public $vehicle_id;
    public $horse_id;
    public $trailer_id;
    public $product_name;

 
    public function mount($ticket){
        $this->ticket = $ticket;
        $this->products = Product::whereIn('department',['inventory','tyre'])->orderBy('name','asc')->get();
        $this->ticket_requests = TicketRequest::where('ticket_id', $this->ticket->id)->latest()->get();
        $this->measurements = Measurement::orderBy('name','asc')->get();
         $this->reset(['search_products']);
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'selectedProduct' => 'required',
        'qty' => 'required',
        'measurement' => 'required',
    ];

    private function resetInputFields(){
        $this->selectedProduct = '';
        $this->measurement = '';
        $this->qty = '';
    }





    public function addProducts(){
   
            $ticket_request = new  TicketRequest;
            $ticket_request->user_id = Auth::user()->id;
            $ticket_request->ticket_id = $this->ticket->id;
            $ticket_request->vehicle_id = $this->ticket->vehicle_id;
            $ticket_request->horse_id = $this->ticket->horse_id;
            $ticket_request->trailer_id = $this->ticket->trailer_id;
            $ticket_request->qty =  $this->qty;
            $ticket_request->product_name = $this->product_name;
            $ticket_request->measurement =  $this->measurement;
            $ticket_request->product_id =  $this->selectedProduct;
            $ticket_request->save();
            
        $this->dispatchBrowserEvent('hide-ticket_requestModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item(s) Requested Successfully!!"
        ]);

   
    }

    public function edit($id){

        $ticket_request = TicketRequest::find($id);
        $this->ticket_id = $ticket_request->ticket_id;
        $this->selectedProduct = $ticket_request->product_id;
        $this->trailer_id = $ticket_request->trailer_id;
        $this->horse_id = $ticket_request->horse_id;
        $this->vehicle_id = $ticket_request->vehicle_id;
        $this->qty = $ticket_request->qty;
        $this->measurement = $ticket_request->measurement;
        $this->ticket_request_id = $ticket_request->id;
        $this->dispatchBrowserEvent('show-ticket_requestEditModal');
    }

    public function update(){

        $ticket_request =  TicketRequest::find($this->ticket_request_id);
        $ticket_request->ticket_id = $this->ticket->id;
        $ticket_request->vehicle_id = $this->ticket->vehicle_id;
         $ticket_request->product_name = $this->product_name;
        $ticket_request->horse_id = $this->ticket->horse_id;
        $ticket_request->trailer_id = $this->ticket->trailer_id;
        $ticket_request->qty =  $this->qty;
        $ticket_request->measurement =  $this->measurement;
        $ticket_request->product_id =  $this->selectedProduct;
        $ticket_request->update();

        $this->dispatchBrowserEvent('hide-ticket_requestEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item(s) Updated Successfully!!"
        ]);
        
    }

 
    public function render()
    {
        if (filled($this->search_products)) {
            $this->products = Product::query()->with('brand')
                                    ->whereIn('department',['inventory','tyre'])
                                     ->where('product_number', 'like', '%'.$this->search_products.'%')
                                     ->orWhere('identification_number', 'like', '%'.$this->search_products.'%')
                                     ->orWhere('name', 'like', '%'.$this->search_products.'%')
                                     ->orWhereHas('brand', function ($query) {
                                        return $query->where('name', 'like', '%'.$this->search_products.'%');
                                     })->get();
            
        }
     
        $this->ticket_requests = TicketRequest::where('ticket_id', $this->ticket->id)->latest()->get();
        return view('livewire.ticket-requests.index',[
            'ticket_requests' => $this->ticket_requests,
            'products' => $this->products,
        ]);
    }
}
