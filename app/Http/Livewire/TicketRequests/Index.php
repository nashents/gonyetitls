<?php

namespace App\Http\Livewire\TicketRequests;


use App\Models\Measurement;
use App\Models\Product;
use App\Models\TicketRequest;
use App\Models\UnitsOfMeasure;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search_products;
    protected $queryString = ['search_products'];
    public $ticket;
    public $ticket_id;
    public $measurements;
    public $measurement;
    public $products;
    public $selectedProduct;
    public $requests;
    protected $ticket_requests;
    public $ticket_request_id;
    public $qty;
    public $tyres;
    public $vehicle_id;
    public $horse_id;
    public $trailer_id;
    public $product_name;
    public $employee_ids = [];
    public $employees;
    public $user;
    public $employee;
    public $units_of_measures;


 
    public function mount($ticket){
        $this->ticket = $ticket;
        $this->qty = 1;
        $this->units_of_measures = UnitsOfMeasure::orderBy('name','asc')->get();
        $this->reset(['search_products']);
         $this->loadProducts();
        $this->user = Auth::user();
        $this->employee = $this->user->employee;
        $this->employees = $this->ticket->booking->employees;
        foreach ($this->employees as $employee) {
            $this->employee_ids[] = $employee->id;
        }
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
    
        'qty' => 'required',
        'measurement' => 'required',
    ];

    private function resetInputFields(){
        $this->selectedProduct = Null;
        $this->measurement = Null;
        $this->qty = Null;
        $this->product_name = Null;
        $this->reset(['search_products']);
        $this->loadProducts();
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
        $this->product_name = $ticket_request->product_name;
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

    public function delete($id){
        $this->ticket_request_id = $id;
        $this->dispatchBrowserEvent('show-deleteModal');
    }

    public function destroy(){
        $ticket_request = TicketRequest::find($this->ticket_request_id);
        $ticket_request->delete();
        $this->dispatchBrowserEvent('hide-deleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Request Item Deleted Successfully!!"
        ]);
    }

    private function loadProducts(){
        $this->products = Product::whereIn('department',['inventory','tyre'])->orderBy('name','asc')->get();
    }

    public function updatedSearchProducts($value){
        $this->products = Product::query()->with('brand')
                        ->whereIn('department',['inventory','tyre'])
                            ->where('product_number', 'like', '%'.$value.'%')
                            ->orWhere('identification_number', 'like', '%'.$value.'%')
                            ->orWhere('name', 'like', '%'.$value.'%')
                            ->orWhereHas('brand', function ($query) use($value) {
                            return $query->where('name', 'like', '%'.$value.'%');
                            })->get();
    }

 
    public function render()
    {
        
        $this->ticket_requests = TicketRequest::where('ticket_id', $this->ticket->id)->latest()->paginate(10);
        return view('livewire.ticket-requests.index',[
            'ticket_requests' => $this->ticket_requests,
            'products' => $this->products,
        ]);
    }
}
