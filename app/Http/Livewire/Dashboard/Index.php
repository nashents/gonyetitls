<?php

namespace App\Http\Livewire\Dashboard;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Fuel;
use App\Models\Rank;
use App\Models\Trip;
use App\Models\Tyre;
use App\Models\Agent;
use App\Models\Asset;
use App\Models\Horse;
use App\Models\Leave;
use App\Models\Branch;
use App\Models\Driver;
use App\Models\Ticket;
use App\Models\Vendor;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Service;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\CashFlow;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Container;
use App\Models\Inventory;
use App\Models\Allocation;
use App\Models\Assignment;
use App\Models\Department;
use App\Models\Destination;
use App\Models\FuelRequest;
use App\Models\Transporter;
use App\Models\DeliveryNote;
use App\Models\DepartmentHead;
use App\Models\TransportOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    public $transporter_count;
    public $transporters ;
    public $agents ;
    public $agent_count ;
    public $department_count ;
    public $rank ;
    public $hods;
    public $trip_count;
    public $horse_count ;
    public $destinations_count ;
    public $trailer_count ;
    public $vendor_count;
    public $vehicle_count ;
    public $assignment_count ;
    public $branch_count ;
    public $customer_count ;
    public $bill_count ;
    public $invoice_count ;
    public $requisition_count;
    public $destination_count;
    public $employee_count ;
    public $driver_count ;
    public $leave_count ;
    public $tyre_count ;
    public $service_count ;
    public $asset_count ;
    public $inventory_count ;
    public $booking_count ;
    public $ticket_count;
    public $fuel_supplier_count ;
    public $fuel_order_count ;
    public $transport_order_count ;
    public $recent_employees ;
    public $containers ;
    public $allocations ;
    public $myallocations ;
    public $petrol_quantity ;
    public $diesel_quantity ;
    public $selectedCurrency ;
    public $currency_name ;
    public $currencies ;
    public $trips ;
    public $bookings ;
    
    public $litreage_moved;
    public $months;
    public $driver_names;
    public $encoded_driver_names;
   



    

    public function mount(){

        // $currentMonth = Carbon::now();
        // $this->current_month = $currentMonth->month;
        // $this->monthName = Carbon::createFromFormat('m', 6)->format('M');

        if (isset(Auth::user()->employee->company->currency)) {
            $this->selectedCurrency = Auth::user()->employee->company->currency->id;
            $this->currency_name = Auth::user()->employee->company->currency->name;
        }else{
            $this->selectedCurrency = 1;
            $this->currency_name = "USD";
        }
      
        // $currentMonth = Carbon::now();

        // for ($i = 0; $i < $currentMonth->month; $i++) {

        //     $month = $currentMonth->copy()->subMonths($i);
        //     $this->litreage_moved = DB::table('trips')
        //         ->whereYear('created_at', '=', date('Y'))
        //         ->whereMonth('created_at', '=', $month->month)
        //         ->where('litreage_at_20', '!=', Null)
        //         ->where('litreage_at_20', '!=', "")
        //         ->sum('litreage_at_20');

        //     $this->months = Carbon::createFromFormat('m', $month->month)->format('M');

        // }
       

        


        $this->jan_litreage_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 1)
        ->where('loaded_litreage_at_20','!=', '')
        ->where('loaded_litreage_at_20','!=', Null)
        ->where('offloaded_litreage_at_20','!=', "")
        ->where('offloaded_litreage_at_20','!=', Null)
        ->sum(DB::raw('loaded_litreage_at_20 - offloaded_litreage_at_20'));

        $this->feb_litreage_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 2)
       ->where('loaded_litreage_at_20','!=', '')
        ->where('loaded_litreage_at_20','!=', Null)
        ->where('offloaded_litreage_at_20','!=', "")
        ->where('offloaded_litreage_at_20','!=', Null)
        ->sum(DB::raw('loaded_litreage_at_20 - offloaded_litreage_at_20'));

        $this->apr_litreage_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 3)
        ->where('loaded_litreage_at_20','!=', '')
        ->where('loaded_litreage_at_20','!=', Null)
        ->where('offloaded_litreage_at_20','!=', "")
        ->where('offloaded_litreage_at_20','!=', Null)
        ->sum(DB::raw('loaded_litreage_at_20 - offloaded_litreage_at_20'));

        $this->mar_litreage_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 4)
        ->where('loaded_litreage_at_20','!=', '')
        ->where('loaded_litreage_at_20','!=', Null)
        ->where('offloaded_litreage_at_20','!=', "")
        ->where('offloaded_litreage_at_20','!=', Null)
        ->sum(DB::raw('loaded_litreage_at_20 - offloaded_litreage_at_20'));

        $this->may_litreage_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 5)
        ->where('loaded_litreage_at_20','!=', '')
        ->where('loaded_litreage_at_20','!=', Null)
        ->where('offloaded_litreage_at_20','!=', "")
        ->where('offloaded_litreage_at_20','!=', Null)
        ->sum(DB::raw('loaded_litreage_at_20 - offloaded_litreage_at_20'));
        
        $this->jun_litreage_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 6)
        ->where('loaded_litreage_at_20','!=', '')
        ->where('loaded_litreage_at_20','!=', Null)
        ->where('offloaded_litreage_at_20','!=', "")
        ->where('offloaded_litreage_at_20','!=', Null)
        ->sum(DB::raw('loaded_litreage_at_20 - offloaded_litreage_at_20'));

        $this->jul_litreage_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 7)
        ->where('loaded_litreage_at_20','!=', '')
        ->where('loaded_litreage_at_20','!=', Null)
        ->where('offloaded_litreage_at_20','!=', "")
        ->where('offloaded_litreage_at_20','!=', Null)
        ->sum(DB::raw('loaded_litreage_at_20 - offloaded_litreage_at_20'));
      

        $this->aug_litreage_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 8)
        ->where('loaded_litreage_at_20','!=', '')
        ->where('loaded_litreage_at_20','!=', Null)
        ->where('offloaded_litreage_at_20','!=', "")
        ->where('offloaded_litreage_at_20','!=', Null)
        ->sum(DB::raw('loaded_litreage_at_20 - offloaded_litreage_at_20'));

        $this->sep_litreage_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 9)
        ->where('loaded_litreage_at_20','!=', '')
        ->where('loaded_litreage_at_20','!=', Null)
        ->where('offloaded_litreage_at_20','!=', "")
        ->where('offloaded_litreage_at_20','!=', Null)
        ->sum(DB::raw('loaded_litreage_at_20 - offloaded_litreage_at_20'));
        
        $this->oct_litreage_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 10)
        ->where('loaded_litreage_at_20','!=', '')
        ->where('loaded_litreage_at_20','!=', Null)
        ->where('offloaded_litreage_at_20','!=', "")
        ->where('offloaded_litreage_at_20','!=', Null)
        ->sum(DB::raw('loaded_litreage_at_20 - offloaded_litreage_at_20'));

        $this->nov_litreage_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 11)
        ->where('loaded_litreage_at_20','!=', '')
        ->where('loaded_litreage_at_20','!=', Null)
        ->where('offloaded_litreage_at_20','!=', "")
        ->where('offloaded_litreage_at_20','!=', Null)
        ->sum(DB::raw('loaded_litreage_at_20 - offloaded_litreage_at_20'));

        $this->dec_litreage_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 12)
        ->where('loaded_litreage_at_20','!=', '')
        ->where('loaded_litreage_at_20','!=', Null)
        ->where('offloaded_litreage_at_20','!=', "")
        ->where('offloaded_litreage_at_20','!=', Null)
        ->sum(DB::raw('loaded_litreage_at_20 - offloaded_litreage_at_20'));

        $this->jan_weight_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 1)
        ->where('loaded_weight','!=', '')
        ->where('loaded_weight','!=', Null)
        ->where('offloaded_weight','!=', "")
        ->where('offloaded_weight','!=', Null)
        ->sum(DB::raw('loaded_weight - offloaded_weight'));

        $this->feb_weight_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 2)
        ->where('loaded_weight','!=', '')
        ->where('loaded_weight','!=', Null)
        ->where('offloaded_weight','!=', "")
        ->where('offloaded_weight','!=', Null)
        ->sum(DB::raw('loaded_weight - offloaded_weight'));

        $this->apr_weight_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 3)
        ->where('loaded_weight','!=', '')
        ->where('loaded_weight','!=', Null)
        ->where('offloaded_weight','!=', "")
        ->where('offloaded_weight','!=', Null)
        ->sum(DB::raw('loaded_weight - offloaded_weight'));

        $this->mar_weight_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 4)
        ->where('loaded_weight','!=', '')
        ->where('loaded_weight','!=', Null)
        ->where('offloaded_weight','!=', "")
        ->where('offloaded_weight','!=', Null)
        ->sum(DB::raw('loaded_weight - offloaded_weight'));

        $this->may_weight_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 5)
        ->where('loaded_weight','!=', '')
        ->where('loaded_weight','!=', Null)
        ->where('offloaded_weight','!=', "")
        ->where('offloaded_weight','!=', Null)
        ->sum(DB::raw('loaded_weight - offloaded_weight'));
        
        $this->jun_weight_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 6)
        ->where('loaded_weight','!=', '')
        ->where('loaded_weight','!=', Null)
        ->where('offloaded_weight','!=', "")
        ->where('offloaded_weight','!=', Null)
        ->sum(DB::raw('loaded_weight - offloaded_weight'));

        $this->jul_weight_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 7)
        ->where('loaded_weight','!=', '')
        ->where('loaded_weight','!=', Null)
        ->where('offloaded_weight','!=', "")
        ->where('offloaded_weight','!=', Null)
        ->sum(DB::raw('loaded_weight - offloaded_weight'));
      

        $this->aug_weight_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 8)
        ->where('loaded_weight','!=', '')
        ->where('loaded_weight','!=', Null)
        ->where('offloaded_weight','!=', "")
        ->where('offloaded_weight','!=', Null)
        ->sum(DB::raw('loaded_weight - offloaded_weight'));

        $this->sep_weight_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 9)
        ->where('loaded_weight','!=', '')
        ->where('loaded_weight','!=', Null)
        ->where('offloaded_weight','!=', "")
        ->where('offloaded_weight','!=', Null)
        ->sum(DB::raw('loaded_weight - offloaded_weight'));
        
        $this->oct_weight_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 10)
        ->where('loaded_weight','!=', '')
        ->where('loaded_weight','!=', Null)
        ->where('offloaded_weight','!=', "")
        ->where('offloaded_weight','!=', Null)
        ->sum(DB::raw('loaded_weight - offloaded_weight'));

        $this->nov_weight_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 11)
        ->where('loaded_weight','!=', '')
        ->where('loaded_weight','!=', Null)
        ->where('offloaded_weight','!=', "")
        ->where('offloaded_weight','!=', Null)
        ->sum(DB::raw('loaded_weight - offloaded_weight'));

        $this->dec_weight_loss = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 12)
        ->where('loaded_weight','!=', '')
        ->where('loaded_weight','!=', Null)
        ->where('offloaded_weight','!=', "")
        ->where('offloaded_weight','!=', Null)
        ->sum(DB::raw('loaded_weight - offloaded_weight'));



        $this->jan_distance = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 1)
        ->where('starting_mileage', '!=', Null)
        ->where('starting_mileage', '!=', "")
        ->where('ending_mileage', '!=', Null)
        ->where('ending_mileage', '!=', "")
        ->sum(DB::raw('ending_mileage - starting_mileage'));

        $this->feb_distance = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 2)
        ->where('starting_mileage', '!=', Null)
        ->where('starting_mileage', '!=', "")
        ->where('ending_mileage', '!=', Null)
        ->where('ending_mileage', '!=', "")
        ->sum(DB::raw('ending_mileage - starting_mileage'));

        $this->mar_distance = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 3)
        ->where('starting_mileage', '!=', Null)
        ->where('starting_mileage', '!=', "")
        ->where('ending_mileage', '!=', Null)
        ->where('ending_mileage', '!=', "")
        ->sum(DB::raw('ending_mileage - starting_mileage'));

        $this->apr_distance = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 4)
        ->where('starting_mileage', '!=', Null)
        ->where('starting_mileage', '!=', "")
        ->where('ending_mileage', '!=', Null)
        ->where('ending_mileage', '!=', "")
        ->sum(DB::raw('ending_mileage - starting_mileage'));

        $this->may_distance = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 5)
        ->where('starting_mileage', '!=', Null)
        ->where('starting_mileage', '!=', "")
        ->where('ending_mileage', '!=', Null)
        ->where('ending_mileage', '!=', "")
        ->sum(DB::raw('ending_mileage - starting_mileage'));

        $this->jun_distance = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 6)
        ->where('starting_mileage', '!=', Null)
        ->where('starting_mileage', '!=', "")
        ->where('ending_mileage', '!=', Null)
        ->where('ending_mileage', '!=', "")
        ->sum(DB::raw('ending_mileage - starting_mileage'));

        $this->jul_distance = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 7)
        ->where('starting_mileage', '!=', Null)
        ->where('starting_mileage', '!=', "")
        ->where('ending_mileage', '!=', Null)
        ->where('ending_mileage', '!=', "")
        ->sum(DB::raw('ending_mileage - starting_mileage'));

        $this->aug_distance = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 8)
        ->where('starting_mileage', '!=', Null)
        ->where('starting_mileage', '!=', "")
        ->where('ending_mileage', '!=', Null)
        ->where('ending_mileage', '!=', "")
        ->sum(DB::raw('ending_mileage - starting_mileage'));

        $this->sep_distance = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 9)
        ->where('starting_mileage', '!=', Null)
        ->where('starting_mileage', '!=', "")
        ->where('ending_mileage', '!=', Null)
        ->where('ending_mileage', '!=', "")
         ->sum(DB::raw('ending_mileage - starting_mileage'));

        $this->oct_distance = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 10)
        ->where('starting_mileage', '!=', Null)
        ->where('starting_mileage', '!=', "")
        ->where('ending_mileage', '!=', Null)
        ->where('ending_mileage', '!=', "")
        ->sum(DB::raw('ending_mileage - starting_mileage'));

        $this->nov_distance = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 11)
        ->where('starting_mileage', '!=', Null)
        ->where('starting_mileage', '!=', "")
        ->where('ending_mileage', '!=', Null)
        ->where('ending_mileage', '!=', "")
        ->sum(DB::raw('ending_mileage - starting_mileage'));

        $this->dec_distance = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 12)
        ->where('starting_mileage', '!=', Null)
        ->where('starting_mileage', '!=', "")
        ->where('ending_mileage', '!=', Null)
        ->where('ending_mileage', '!=', "")
        ->sum(DB::raw('ending_mileage - starting_mileage'));

        $male = "Male";
        $female = "Female";

        // $this->all_drivers = Driver::all();
        // foreach( $this->all_drivers as $driver){

        //     $this->driver_names = [
        //         $driver->employee->name ." ". $driver->employee->surname, 25
        //     ] ;
           
        // }
        $this->encoded_driver_names = json_encode($this->driver_names);
        // $this->encoded_driver_names = implode(', ', $this->driver_names);


        $this->top_drivers = Driver::select('drivers.id', DB::raw('COUNT(trips.id) as trips_count'))
        ->join('trips', 'drivers.id', '=', 'trips.driver_id')
        ->whereYear('trips.start_date', date('Y'))
        ->whereMonth('trips.start_date', now()->month)
        ->where('trips.trip_status', 'Offloaded')
        ->where('trips.deleted_at', Null)
        ->where('trips.authorization','approved')
        ->groupBy('drivers.id')
        ->orderByDesc('trips_count')
        ->limit(5)
        ->get();
     
        $this->top_horses = Horse::select('horses.id', DB::raw('COUNT(trips.id) as trips_count'))
        ->join('trips', 'horses.id', '=', 'trips.horse_id')
        ->whereYear('trips.start_date', date('Y'))
        ->whereMonth('trips.start_date', now()->month)
        ->where('trips.trip_status', 'Offloaded')
        ->where('trips.deleted_at', Null)
        ->where('trips.authorization','approved')
        ->groupBy('horses.id')
        ->orderByDesc('trips_count')
        ->limit(5)
        ->get();

    
        $this->resignation_2022 = Employee::whereYear('end_date', '2022')->get()->count();
        $this->resignation_2023 = Employee::whereYear('end_date', '2023')->get()->count();
        $this->resignation_2024 = Employee::whereYear('end_date', '2024')->get()->count();
        $this->resignation_2025 = Employee::whereYear('end_date', '2025')->get()->count();

        $this->males = Employee::where('status',1)->where('gender', 'LIKE', '%'.$male.'%')->get()->count();
        $this->females = Employee::where('status',1)->where('gender', 'LIKE', '%'.$female.'%')->get()->count();

        $this->jan_sales = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 1)->where('freight',"!=",Null)->where('freight',"!=","")->sum('freight');
        $this->feb_sales = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 2)->where('freight',"!=",Null)->where('freight',"!=","")->sum('freight');
        $this->mar_sales = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 3)->where('freight',"!=",Null)->where('freight',"!=","")->sum('freight');
        $this->apr_sales = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 4)->where('freight',"!=",Null)->where('freight',"!=","")->sum('freight');
        $this->may_sales = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 5)->where('freight',"!=",Null)->where('freight',"!=","")->sum('freight');
        $this->jun_sales = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 6)->where('freight',"!=",Null)->where('freight',"!=","")->sum('freight');
        $this->jul_sales = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 7)->where('freight',"!=",Null)->where('freight',"!=","")->sum('freight');
        $this->aug_sales = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 8)->where('freight',"!=",Null)->where('freight',"!=","")->sum('freight');
        $this->sep_sales = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 9)->where('freight',"!=",Null)->where('freight',"!=","")->sum('freight');
        $this->oct_sales = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 10)->where('freight',"!=",Null)->where('freight',"!=","")->sum('freight');
        $this->nov_sales = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 11)->where('freight',"!=",Null)->where('freight',"!=","")->sum('freight');
        $this->dec_sales = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 12)->where('start_date',"!=",Null)->where('freight',"!=","")->sum('freight');

        

        $this->jan_trips = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 1)->get()->count();
        $this->feb_trips = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 2)->get()->count();
        $this->mar_trips = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 3)->get()->count();
        $this->apr_trips = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 4)->get()->count();
        $this->may_trips = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 5)->get()->count();
        $this->jun_trips = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 6)->get()->count();
        $this->jul_trips = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 7)->get()->count();
        $this->aug_trips = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 8)->get()->count();
        $this->sep_trips = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 9)->get()->count();
        $this->oct_trips = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 10)->get()->count();
        $this->nov_trips = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 11)->get()->count();
        $this->dec_trips = Trip::whereYear('start_date', date('Y'))
        ->whereMonth('start_date', 12)->get()->count();
       
      
        
        
        $this->jan_litreage = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 1)->where('loaded_litreage_at_20',"!=",Null)->where('loaded_litreage_at_20',"!=","")->sum('loaded_litreage_at_20');
        $this->feb_litreage = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 2)->where('loaded_litreage_at_20',"!=",Null)->where('loaded_litreage_at_20',"!=","")->sum('loaded_litreage_at_20');
        $this->mar_litreage = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 3)->where('loaded_litreage_at_20',"!=",Null)->where('loaded_litreage_at_20',"!=","")->sum('loaded_litreage_at_20');
        $this->apr_litreage = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 4)->where('loaded_litreage_at_20',"!=",Null)->where('loaded_litreage_at_20',"!=","")->sum('loaded_litreage_at_20');
        $this->may_litreage = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 5)->where('loaded_litreage_at_20',"!=",Null)->where('loaded_litreage_at_20',"!=","")->sum('loaded_litreage_at_20');
        $this->jun_litreage = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 6)->where('loaded_litreage_at_20',"!=",Null)->where('loaded_litreage_at_20',"!=","")->sum('loaded_litreage_at_20');
        $this->jul_litreage = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 7)->where('loaded_litreage_at_20',"!=",Null)->where('loaded_litreage_at_20',"!=","")->sum('loaded_litreage_at_20');
        $this->aug_litreage = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 8)->where('loaded_litreage_at_20',"!=",Null)->where('loaded_litreage_at_20',"!=","")->sum('loaded_litreage_at_20');
        $this->sep_litreage = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 9)->where('loaded_litreage_at_20',"!=",Null)->where('loaded_litreage_at_20',"!=","")->sum('loaded_litreage_at_20');
        $this->oct_litreage = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 10)->where('loaded_litreage_at_20',"!=",Null)->where('loaded_litreage_at_20',"!=","")->sum('loaded_litreage_at_20');
        $this->nov_litreage = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 11)->where('loaded_litreage_at_20',"!=",Null)->where('loaded_litreage_at_20',"!=","")->sum('loaded_litreage_at_20');
        $this->dec_litreage = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 12)->where('loaded_litreage_at_20',"!=",Null)->where('loaded_litreage_at_20',"!=","")->sum('loaded_litreage_at_20');
       
        

        $this->jan_weight = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 1)->where('loaded_weight',"!=",Null)->where('loaded_weight',"!=","")->sum('loaded_weight');
        $this->feb_weight = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 2)->where('loaded_weight',"!=",Null)->where('loaded_weight',"!=","")->sum('loaded_weight');
        $this->mar_weight = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 3)->where('loaded_weight',"!=",Null)->where('loaded_weight',"!=","")->sum('loaded_weight');
        $this->apr_weight = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 4)->where('loaded_weight',"!=",Null)->where('loaded_weight',"!=","")->sum('loaded_weight');
        $this->may_weight = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 5)->where('loaded_weight',"!=",Null)->where('loaded_weight',"!=","")->sum('loaded_weight');
        $this->jun_weight = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 6)->where('loaded_weight',"!=",Null)->where('loaded_weight',"!=","")->sum('loaded_weight');
        $this->jul_weight = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 7)->where('loaded_weight',"!=",Null)->where('loaded_weight',"!=","")->sum('loaded_weight');
        $this->aug_weight = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 8)->where('loaded_weight',"!=",Null)->where('loaded_weight',"!=","")->sum('loaded_weight');
        $this->sep_weight = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 9)->where('loaded_weight',"!=",Null)->where('loaded_weight',"!=","")->sum('loaded_weight');
        $this->oct_weight = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 10)->where('loaded_weight',"!=",Null)->where('loaded_weight',"!=","")->sum('loaded_weight');
        $this->nov_weight = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 11)->where('loaded_weight',"!=",Null)->where('loaded_weight',"!=","")->sum('loaded_weight');
        $this->dec_weight = DeliveryNote::whereYear('offloaded_date', date('Y'))
        ->whereMonth('offloaded_date', 12)->where('loaded_weight',"!=",Null)->where('loaded_weight',"!=","")->sum('loaded_weight');


        $this->jan_open_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 1)->where('status', 1)->get()->count();
        $this->jan_closed_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 1)->where('status', 0)->get()->count();
        $this->feb_open_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 2)->where('status', 1)->get()->count();
        $this->feb_closed_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 2)->where('status', 0)->get()->count();
        $this->mar_open_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 3)->where('status', 1)->get()->count();
        $this->mar_closed_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 3)->where('status', 0)->get()->count();
        $this->apr_open_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 4)->where('status', 1)->get()->count();
        $this->apr_closed_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 4)->where('status', 0)->get()->count();
        $this->may_open_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 5)->where('status', 1)->get()->count();
        $this->may_closed_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 5)->where('status', 0)->get()->count();
        $this->jun_open_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 6)->where('status', 1)->get()->count();
        $this->jun_closed_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 6)->where('status', 0)->get()->count();
        $this->jul_open_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 7)->where('status', 1)->get()->count();
        $this->jul_closed_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 7)->where('status', 0)->get()->count();
        $this->aug_open_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 8)->where('status', 1)->get()->count();
        $this->aug_closed_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 8)->where('status', 0)->get()->count();
        $this->sep_open_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 9)->where('status', 1)->get()->count();
        $this->sep_closed_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 9)->where('status', 0)->get()->count();
        $this->oct_open_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 10)->where('status', 1)->get()->count();
        $this->oct_closed_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 10)->where('status', 0)->get()->count();
        $this->nov_open_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 11)->where('status', 1)->get()->count();
        $this->nov_closed_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 11)->where('status', 0)->get()->count();
        $this->dec_open_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 12)->where('status', 1)->get()->count();
        $this->dec_closed_bookings = Booking::whereYear('created_at', date('Y'))->whereMonth('created_at', 12)->where('status', 0)->get()->count();

       

        $this->jan_initial_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 1)->where('fillup', 1)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->feb_initial_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 2)->where('fillup', 1)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->mar_initial_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 3)->where('fillup', 1)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->apr_initial_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 4)->where('fillup', 1)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->may_initial_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 5)->where('fillup', 1)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->jun_initial_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 6)->where('fillup', 1)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->jul_initial_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 7)->where('fillup', 1)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->aug_initial_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 8)->where('fillup', 1)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->sep_initial_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 9)->where('fillup', 1)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->oct_initial_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 10)->where('fillup', 1)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->nov_initial_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 11)->where('fillup', 1)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->dec_initial_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 12)->where('fillup', 1)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
     
        $this->jan_topup_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 1)->where('fillup', 0)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->feb_topup_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 2)->where('fillup', 0)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->mar_topup_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 3)->where('fillup', 0)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->apr_topup_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 4)->where('fillup', 0)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->may_topup_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 5)->where('fillup', 0)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->jun_topup_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 6)->where('fillup', 0)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->jul_topup_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 7)->where('fillup', 0)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->aug_topup_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 8)->where('fillup', 0)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->sep_topup_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 9)->where('fillup', 0)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->oct_topup_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 10)->where('fillup', 0)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->nov_topup_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 11)->where('fillup', 0)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
        $this->dec_topup_fuel = Fuel::whereYear('created_at', date('Y'))
        ->whereMonth('created_at', 12)->where('fillup', 0)->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');

 
    
    
   
        $this->transporter_count = Transporter::all()->count();
        $this->transporters = Transporter::latest()->take(5)->get();
        $this->agents = Agent::latest()->take(5)->get();
     
        $this->bookings = Booking::latest()->take(5)->get();
        $this->fuel_orders = Fuel::latest()->take(5)->get();
        $this->agent_count = Agent::all()->count();
        $this->department_count = Department::all()->count();
        $this->rank = Rank::where('name','HOD')->first();
        $this->hods = DepartmentHead::all();
        $this->trip_count = Trip::whereYear('start_date',date('Y'))->where('deleted_at', Null)->get()->count();
        $this->horse_count = Horse::where('archive',false)->get()->count();
        $this->destinations_count = Destination::all()->count();
        $this->trailer_count = Trailer::where('archive',false)->get()->count();
        $this->vendor_count = Vendor::where('status',true)->get()->count();
        $this->vehicle_count = Vehicle::where('archive',false)->get()->count();
        $this->assignment_count = Assignment::all()->count();
        $this->branch_count = Branch::all()->count();
        $this->customer_count = Customer::where('status',true)->get()->count();
        $this->bill_count = Bill::whereYear('bill_date',date('Y'))->get()->count();
        $this->invoice_count = Invoice::whereYear('date',date('Y'))->get()->count();
        $this->employee_count = Employee::doesntHave('driver')->where('archive',false)->get()->count();
        $this->driver_count = Driver::where('archive',false)->get()->count();
        $this->tyre_count = Tyre::where('disposed',0)->where('status',1)->get()->count();
        $this->service_count = Service::all()->count();
        $this->inventory_count = Inventory::where('disposed',0)->where('status',1)->get()->count();
        $this->product_count = Product::where('buy',True)->where('department','inventory')->where('status',1)->get()->count();
        $this->booking_count = Booking::where('authorization','approved')->whereYear('created_at', date('Y'))->count();
        $this->ticket_count = Ticket::whereYear('created_at', date('Y'))->count();
        $this->fuel_supplier_count = Container::all()->count();
        $this->fuel_order_count = Fuel::whereYear('created_at', date('Y'))->count();
        $this->transport_order_count = TransportOrder::whereYear('created_at', date('Y'))->count();
        $this->recent_employees = Employee::latest()->take('5')->get();
        $this->containers = Container::latest()->get();
        $this->allocations = Allocation::latest()->take('5')->get();
        $this->myallocations = Allocation::where('employee_id', Auth::user()->employee->id)->latest()->take('5')->get();
        $this->petrol_quantity = Container::where('fuel_type','Petrol')->sum('balance');
        $this->diesel_quantity = Container::where('fuel_type','Diesel')->sum('balance');
    }




    public function render()
    {

        $this->currencies = Currency::orderBy('name','asc')->get();

        $this->jan = Invoice::whereYear('date', date('Y'))
        ->whereMonth('date', 1)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->feb = Invoice::whereYear('date', date('Y'))
        ->whereMonth('date', 2)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->mar = Invoice::whereYear('date', date('Y'))
        ->whereMonth('date', 3)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->apr = Invoice::whereYear('date', date('Y'))
        ->whereMonth('date', 4)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->may = Invoice::whereYear('date', date('Y'))
        ->whereMonth('date', 5)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->jun = Invoice::whereYear('date', date('Y'))
        ->whereMonth('date', 6)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->jul = Invoice::whereYear('date', date('Y'))
        ->whereMonth('date', 7)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->aug = Invoice::whereYear('date', date('Y'))
        ->whereMonth('date', 8)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->sep = Invoice::whereYear('date', date('Y'))
        ->whereMonth('date', 9)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->oct = Invoice::whereYear('date', date('Y'))
        ->whereMonth('date', 10)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->nov = Invoice::whereYear('date', date('Y'))
        ->whereMonth('date', 11)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->dec = Invoice::whereYear('date', date('Y'))
        ->whereMonth('date', 12)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
    

        $this->jan_expense = Bill::whereYear('bill_date', date('Y'))
        ->whereMonth('bill_date', 1)->where('currency_id', $this->selectedCurrency)->where('to_be_paid',True)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->feb_expense = Bill::whereYear('bill_date', date('Y'))
        ->whereMonth('bill_date', 2)->where('currency_id', $this->selectedCurrency)->where('to_be_paid',True)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->mar_expense = Bill::whereYear('bill_date', date('Y'))
        ->whereMonth('bill_date', 3)->where('currency_id', $this->selectedCurrency)->where('to_be_paid',True)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->apr_expense = Bill::whereYear('bill_date', date('Y'))
        ->whereMonth('bill_date', 4)->where('currency_id', $this->selectedCurrency)->where('to_be_paid',True)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->may_expense = Bill::whereYear('bill_date', date('Y'))
        ->whereMonth('bill_date', 5)->where('currency_id', $this->selectedCurrency)->where('to_be_paid',True)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->jun_expense = Bill::whereYear('bill_date', date('Y'))
        ->whereMonth('bill_date', 6)->where('currency_id', $this->selectedCurrency)->where('to_be_paid',True)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->jul_expense = Bill::whereYear('bill_date', date('Y'))
        ->whereMonth('bill_date', 7)->where('currency_id', $this->selectedCurrency)->where('to_be_paid',True)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->aug_expense = Bill::whereYear('bill_date', date('Y'))
        ->whereMonth('bill_date', 8)->where('currency_id', $this->selectedCurrency)->where('to_be_paid',True)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->sep_expense = Bill::whereYear('bill_date', date('Y'))
        ->whereMonth('bill_date', 9)->where('currency_id', $this->selectedCurrency)->where('to_be_paid',True)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->oct_expense = Bill::whereYear('bill_date', date('Y'))
        ->whereMonth('bill_date', 10)->where('currency_id', $this->selectedCurrency)->where('to_be_paid',True)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->nov_expense = Bill::whereYear('bill_date', date('Y'))
        ->whereMonth('bill_date', 11)->where('currency_id', $this->selectedCurrency)->where('to_be_paid',True)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->dec_expense = Bill::whereYear('bill_date', date('Y'))
        ->whereMonth('bill_date', 12)->where('currency_id', $this->selectedCurrency)->where('to_be_paid',True)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
    
        $this->income_2021 = Invoice::whereYear('date', '2021')->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->expenses_2021 = Bill::whereYear('bill_date', '2021')->where('to_be_paid', True)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->income_2022 = Invoice::whereYear('date', '2022')->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->expenses_2022 = Bill::whereYear('bill_date', '2022')->where('to_be_paid', True)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->income_2023 = Invoice::whereYear('date', '2023')->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->expenses_2023 = Bill::whereYear('bill_date', '2023')->where('to_be_paid', True)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->income_2024 = Invoice::whereYear('date', '2024')->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->expenses_2024 = Bill::whereYear('bill_date', '2024')->where('to_be_paid', True)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->income_2025 = Invoice::whereYear('date', '2025')->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->expenses_2025 = Bill::whereYear('bill_date', '2025')->where('to_be_paid', True)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');

        return view('livewire.dashboard.index');
    }
}
