<?php

namespace App\Http\Livewire\Rentals;

use App\Models\Driver;
use App\Models\Rental;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Transporter;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\RentalsExport;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;
    public $search;
    public $from;
    public $to;
    protected $queryString = ['search'];
    protected $paginationTheme = 'bootstrap';


    public $customers;
    public $selectedCustomer;
    public $transporters;
    public $selectedTransporter;
    public $drivers;
    public $driver_id;
    public $vehicles;
    public $selectedVehicle;
    protected $rentals;
    public $rental_id;
    public $status;
    public $selected_rental;
  
    public $rate_amount;
    public $deposit_amount;
    public $currencies;
    public $selectedCurrency;
    public $notes;
    public $pickup_at;
    public $due_back_at;
    public $returned_at;
    public $pickup_odometer;
    public $return_odometer;
    public $pickup_fuel_level;
    public $return_fuel_level;
    public $user;
    public $employee;
    public $company;

    public function mount()
    {
        $this->user = Auth::user();
        $this->employee = $this->user->employee;
        $this->company = $this->employee->company;
        $this->transporters = Transporter::orderBy('name', 'asc')->get();
        $this->currencies = Currency::orderBy('name', 'asc')->get();
        $this->customers = Customer::orderBy('name', 'asc')->get();
        $this->vehicles = collect();
        $this->drivers = collect();
    }

    public function exportRentalsCSV(Excel $excel){
        return $excel->download(new RentalsExport, 'rentals_'.time().'.csv', Excel::CSV);
    }
    public function exportRentalsPDF(Excel $excel){
        return $excel->download(new RentalsExport, 'rentals_'.time().'.pdf', Excel::DOMPDF);
    }
    public function exportRentalsExcel(Excel $excel){
        return $excel->download(new RentalsExport, 'rentals_'.time().'.xlsx');
    }

    public function updatedSelectedTransporter($transporterId)
    {
        $this->vehicles = Vehicle::where('transporter_id', $transporterId)->orderBy('registration_number', 'asc')->get();
    }
    public function updatedSelectedCustomer($customerId)
    {
        $this->drivers = Driver::where('customer_id', $customerId)->orderBy('name', 'asc')->orderBy('surname', 'asc')->get();
    }

     public function rentalNumber(){

        $str = Auth::user()->employee->company->name;
        $words = explode(' ', $str);
        if (isset($words[1][0])) {
            $initials = $words[0][0].$words[1][0];
        }else {
            $initials = $words[0][0];
        }

        $rental = Rental::orderBy('id','desc')->first();

        if (!$rental) {
            $rental_number =  $initials .'R'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $rental->id + 1;
            $rental_number =  $initials .'R'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $rental_number;

    }

    private function resetInputFields(){
        $this->selectedCurrency = null;
        $this->selectedCustomer = null;
        $this->selectedTransporter = null;
        $this->driver_id = null;
        $this->selectedVehicle = null;
        $this->rate_amount = "";
        $this->deposit_amount = "";
        $this->notes = "";
        $this->pickup_at = "";
        $this->due_back_at = "";
        $this->returned_at = "";
        $this->pickup_odometer = "";
        $this->return_odometer = "";
        $this->pickup_fuel_level = "";
        $this->return_fuel_level = "";
      
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'selectedTransporter' => 'required',
        'selectedCustomer' => 'required',
        'selectedVehicle' => 'required',
        'selectedCurrency' => 'required',
        'rate_amount' => 'required|numeric|min:0',
        'deposit_amount' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string',
        'pickup_at' => 'required|date',
        'due_back_at' => 'required|date',
        'returned_at' => 'nullable|date',
        'pickup_odometer' => 'nullable|numeric|min:0',
        'return_odometer' => 'nullable|numeric|min:0',
        'pickup_fuel_level' => 'nullable|numeric|min:0|max:100',
        'return_fuel_level' => 'nullable|numeric|min:0|max:100',
        'status' => 'required|string',
    ];

    public function store(){

        $this->validate();
        
        $rental = new Rental();
        $rental->rental_number = $this->rentalNumber();
        $rental->customer_id = $this->selectedCustomer;
        $rental->transporter_id = $this->selectedTransporter;
        $rental->driver_id = $this->driver_id;
        $rental->vehicle_id = $this->selectedVehicle;
        $rental->currency_id = $this->selectedCurrency;
        $rental->rate_amount = $this->rate_amount;
        $rental->deposit_amount = $this->deposit_amount;
        $rental->notes = $this->notes;
        $rental->pickup_at = $this->pickup_at;
        $rental->due_back_at = $this->due_back_at;
        $rental->returned_at = $this->returned_at;
        $rental->pickup_odometer = $this->pickup_odometer;
        $rental->return_odometer = $this->return_odometer;
        $rental->pickup_fuel_level = $this->pickup_fuel_level;
        $rental->return_fuel_level = $this->return_fuel_level;
        $rental->status = $this->status;
        $rental->save();

        $this->dispatchBrowserEvent('hide-rentalModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Rental Created Successfully!!"
        ]);

    }

    public function edit($id){
        $rental = Rental::findOrFail($id);
        $this->rental_id = $id;
        $this->selectedCustomer = $rental->customer_id;
        $this->selectedTransporter = $rental->transporter_id;
        $this->driver_id = $rental->driver_id;
        $this->selectedVehicle = $rental->vehicle_id;
        $this->selectedCurrency = $rental->currency_id;
        $this->rate_amount = $rental->rate_amount;
        $this->deposit_amount = $rental->deposit_amount;
        $this->notes = $rental->notes;
        $this->pickup_at = $rental->pickup_at;
        $this->due_back_at = $rental->due_back_at;
        $this->returned_at = $rental->returned_at;
        $this->pickup_odometer = $rental->pickup_odometer;
        $this->return_odometer = $rental->return_odometer;
        $this->pickup_fuel_level = $rental->pickup_fuel_level;
        $this->return_fuel_level = $rental->return_fuel_level;
        $this->status = $rental->status;

         $this->dispatchBrowserEvent('show-rentalEditModal');
    }
  
    public function update(){

        $this->validate();

        $rental = Rental::find($this->rental_id);
        $rental->customer_id = $this->selectedCustomer;
        $rental->transporter_id = $this->selectedTransporter;
        $rental->driver_id = $this->driver_id;
        $rental->vehicle_id = $this->selectedVehicle;
        $rental->currency_id = $this->selectedCurrency;
        $rental->rate_amount = $this->rate_amount;
        $rental->deposit_amount = $this->deposit_amount;
        $rental->notes = $this->notes;
        $rental->pickup_at = $this->pickup_at;
        $rental->due_back_at = $this->due_back_at;
        $rental->returned_at = $this->returned_at;
        $rental->pickup_odometer = $this->pickup_odometer;
        $rental->return_odometer = $this->return_odometer;
        $rental->pickup_fuel_level = $this->pickup_fuel_level;
        $rental->return_fuel_level = $this->return_fuel_level;
        $rental->status = $this->status;
        $rental->save();

        $this->dispatchBrowserEvent('hide-rentalModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Rental Created Successfully!!"
        ]);

    }

    public function delete($id){
        $this->rental_id = $id;
        $this->selected_rental = Rental::find($id);
        $this->dispatchBrowserEvent('show-rentalDeleteModal');
    }   

    public function destroy(){
        Rental::find($this->rental_id)->delete();
        $this->dispatchBrowserEvent('hide-rentalDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Rental Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $base = Rental::query()->with('customer', 'transporter', 'currency','vehicle', 'driver');
        $this->rentals = $base->orderByDesc('created_at')->paginate(10);
        return view('livewire.rentals.index',[
            'rentals' => $this->rentals
        ]);
    }
}
