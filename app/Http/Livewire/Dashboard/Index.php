<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\Agent;
use App\Models\Allocation;

use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Bill;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Breakdown;

use App\Models\Checklist;
use App\Models\Container;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Department;
use App\Models\DepartmentHead;
use App\Models\Destination;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\Fuel;

use App\Models\Horse;
use App\Models\Inspection;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\Leave;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Rank;
use App\Models\Recovery;
use App\Models\Service;
use App\Models\Shift;
use App\Models\Ticket;
use App\Models\Trailer;
use App\Models\Transporter;
use App\Models\TransportOrder;
use App\Models\Trip;
use App\Models\Tyre;
use App\Models\Vehicle;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{

    public $shift_count;
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
    public $employee_count;
    public $attendance_count;
    public $employee;
    public $driver;
    public $user;
    public $company ;
    public $driver_count ;
    public $driver_inspections ;
    public $driver_recoveries ;
    public $driver_trips ;
    public $driver_breakdowns ;
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
    public $inventory_purchases_count ;
    public $inventory_dispatches_count ;
    public $workshop_inspection_count ;
    public $my_inspections_count ;
    public $my_tickets_count ;
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
    public $inspection_count ;
    
    public $litreage_moved;
    public $months;
    public $driver_names;
    public $encoded_driver_names;
    public $fuel_orders;
    public $product_count;

    public $jan_litreage_loss;
    public $feb_litreage_loss;
    public $mar_litreage_loss;
    public $apr_litreage_loss;
    public $may_litreage_loss;
    public $jun_litreage_loss;
    public $jul_litreage_loss;
    public $aug_litreage_loss;
    public $sep_litreage_loss;
    public $oct_litreage_loss;
    public $nov_litreage_loss;
    public $dec_litreage_loss;
    
    public $jan_weight_loss;
    public $feb_weight_loss;
    public $mar_weight_loss;
    public $apr_weight_loss;
    public $may_weight_loss;
    public $jun_weight_loss;
    public $jul_weight_loss;
    public $aug_weight_loss;
    public $sep_weight_loss;
    public $oct_weight_loss;
    public $nov_weight_loss;
    public $dec_weight_loss;

    public $jan_distance;
    public $feb_distance;
    public $mar_distance;
    public $apr_distance;
    public $may_distance;
    public $jun_distance;
    public $jul_distance;
    public $aug_distance;
    public $sep_distance;
    public $oct_distance;
    public $nov_distance;
    public $dec_distance;

    public $top_drivers;
    public $top_horses;
    public $resignation_2022;
    public $resignation_2023;
    public $resignation_2024;
    public $resignation_2025;
    public $males;
    public $females;

    public $jan_sales;
    public $feb_sales;
    public $mar_sales;
    public $apr_sales;
    public $may_sales;
    public $jun_sales;
    public $jul_sales;
    public $aug_sales;
    public $sep_sales;
    public $oct_sales;
    public $nov_sales;
    public $dec_sales;

    public $jan_trips;
    public $feb_trips;  
    public $mar_trips;
    public $apr_trips;
    public $may_trips;
    public $jun_trips;
    public $jul_trips;
    public $aug_trips;
    public $sep_trips;
    public $oct_trips;
    public $nov_trips;
    public $dec_trips;

    public $jan_litreage;
    public $feb_litreage;
    public $mar_litreage;
    public $apr_litreage;
    public $may_litreage;
    public $jun_litreage;
    public $jul_litreage;
    public $aug_litreage;
    public $sep_litreage;
    public $oct_litreage;
    public $nov_litreage;
    public $dec_litreage;
   
    public $jan_weight;
    public $feb_weight;
    public $mar_weight;
    public $apr_weight;
    public $may_weight;
    public $jun_weight;
    public $jul_weight;
    public $aug_weight;
    public $sep_weight;
    public $oct_weight;
    public $nov_weight;
    public $dec_weight;

    public $jan_open_bookings;
    public $feb_open_bookings;
    public $mar_open_bookings;
    public $apr_open_bookings;
    public $may_open_bookings;
    public $jun_open_bookings;
    public $jul_open_bookings;
    public $aug_open_bookings;
    public $sep_open_bookings;
    public $oct_open_bookings;
    public $nov_open_bookings;
    public $dec_open_bookings;

    public $jan_closed_bookings;
    public $feb_closed_bookings;
    public $mar_closed_bookings;
    public $apr_closed_bookings;
    public $may_closed_bookings;
    public $jun_closed_bookings;
    public $jul_closed_bookings;
    public $aug_closed_bookings;
    public $sep_closed_bookings;
    public $oct_closed_bookings;
    public $nov_closed_bookings;
    public $dec_closed_bookings;

    public $jan_initial_fuel;
    public $feb_initial_fuel;
    public $mar_initial_fuel;
    public $apr_initial_fuel;
    public $may_initial_fuel;
    public $jun_initial_fuel;
    public $jul_initial_fuel;
    public $aug_initial_fuel;
    public $sep_initial_fuel;
    public $oct_initial_fuel;
    public $nov_initial_fuel;
    public $dec_initial_fuel;

    public $jan_topup_fuel;
    public $feb_topup_fuel;
    public $mar_topup_fuel;
    public $apr_topup_fuel;
    public $may_topup_fuel;
    public $jun_topup_fuel;
    public $jul_topup_fuel;
    public $aug_topup_fuel;
    public $sep_topup_fuel;
    public $oct_topup_fuel;
    public $nov_topup_fuel;
    public $dec_topup_fuel;

    public $jan;
    public $feb;
    public $mar;
    public $apr;
    public $may;
    public $jun;
    public $jul;
    public $aug;
    public $sep;
    public $oct;
    public $nov;
    public $dec;

    public $jan_expense;
    public $feb_expense;
    public $mar_expense;
    public $apr_expense;
    public $may_expense;
    public $jun_expense;
    public $jul_expense;
    public $aug_expense;
    public $sep_expense;
    public $oct_expense;
    public $nov_expense;
    public $dec_expense;

    public $income_2021;
    public $income_2022;
    public $income_2023;
    public $income_2024;
    public $income_2025;
    public $income_2026;
    public $expenses_2021;
    public $expenses_2022;
    public $expenses_2023;
    public $expenses_2024;
    public $expenses_2025;
    public $expenses_2026;

    public $company_currency;
    public $chartData;
    public $year;





    

    public function mount(){

        // $currentMonth = Carbon::now();
        // $this->current_month = $currentMonth->month;
        // $this->monthName = Carbon::createFromFormat('m', 6)->format('M');

        $this->user = Auth::user();
        $this->employee = $this->user->employee;
        $this->driver = $this->employee->driver;
        $this->company = $this->employee->company;
        $this->company_currency = $this->company->currency;

        if (isset($this->company->currency)) {
            $this->selectedCurrency = $this->company->currency->id;
            $this->currency_name = $this->company->currency->name;
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

    $currencyId = $this->company_currency?->id ?? 2;
      $this->top_drivers = Driver::query()
        ->select([
            'drivers.id',
            'drivers.employee_id', // ✅ required for ->employee relationship
            DB::raw('COUNT(trips.id) as trips_count'),
            DB::raw("
                COALESCE(SUM(
                    CASE
                        WHEN trips.currency_id = {$currencyId}
                            THEN NULLIF(trips.freight, '') + 0
                        ELSE
                            NULLIF(trips.exchange_customer_freight, '') + 0
                    END
                ), 0) as total_revenue
            "),
        ])
        ->join('trips', 'drivers.id', '=', 'trips.driver_id')
        ->whereYear('trips.start_date', now()->year)
        ->whereMonth('trips.start_date', now()->month)
        ->where('trips.trip_status', 'Offloaded')
        ->where('trips.authorization', 'approved')
        ->whereNull('trips.deleted_at')
        ->with(['employee:id,employee_number,name,surname'])
        ->groupBy('drivers.id', 'drivers.employee_id') // ✅ include it here too
        ->orderByDesc('trips_count')
        ->limit(5)
        ->get();
        
        $this->top_horses = Horse::query()
        ->select([
            'horses.id',
            'horses.horse_number',
            'horses.registration_number',
            'horses.fleet_number',
            'horses.horse_make_id',
            'horses.horse_model_id',

            DB::raw('COUNT(DISTINCT trips.id) as trips_count'),

            // Fuel usage for the same month (only where trip_id is present)
            DB::raw("
                COALESCE(SUM(DISTINCT
                    CASE
                        WHEN fuels.id IS NOT NULL
                            AND fuels.trip_id IS NOT NULL
                            AND fuels.trip_id != ''
                            AND fuels.quantity IS NOT NULL
                            AND fuels.quantity != ''
                        THEN fuels.quantity + 0
                        ELSE 0
                    END
                ), 0) as fuel_usage
            "),

            // Revenue using your rule: default currency uses freight, other uses exchange_customer_freight
            DB::raw("
                COALESCE(SUM(
                    CASE
                        WHEN trips.currency_id = {$currencyId}
                            THEN NULLIF(trips.freight, '') + 0
                        ELSE
                            NULLIF(trips.exchange_customer_freight, '') + 0
                    END
                ), 0) as total_revenue
            "),
        ])
        ->join('trips', 'horses.id', '=', 'trips.horse_id')
        ->leftJoin('fuels', function ($join) {
            $join->on('fuels.horse_id', '=', 'horses.id')
                ->whereMonth('fuels.date', now()->month)
                ->whereYear('fuels.date', now()->year);
        })
        ->whereYear('trips.start_date', now()->year)
        ->whereMonth('trips.start_date', now()->month)
        ->where('trips.trip_status', 'Offloaded')
        ->where('trips.authorization', 'approved')
        ->whereNull('trips.deleted_at')
        ->with([
            'horse_make:id,name',
            'horse_model:id,name',
        ])
        ->groupBy(
            'horses.id',
            'horses.horse_number',
            'horses.registration_number',
            'horses.fleet_number',
            'horses.horse_make_id',
            'horses.horse_model_id'
        )
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

 
    
    
   
        $this->shift_count = Shift::all()->count();
        $this->transporter_count = Transporter::all()->count();
        $this->transporters = Transporter::latest()->take(5)->get();
        $this->agents = Agent::latest()->take(5)->get();
     
        $this->bookings = Booking::latest()->take(5)->get();
        $this->fuel_orders = Fuel::latest()->take(5)->get();
        $this->agent_count = Agent::all()->count();
        $this->department_count = Department::all()->count();
        $this->rank = Rank::where('name','HOD')->first();
        $this->hods = DepartmentHead::all();
        $this->trip_count = Trip::whereYear('start_date', date('Y'))->where('deleted_at', Null)->get()->count();
        if($this->driver){
             $this->driver_inspections = Checklist::whereYear('date',date('Y'))->where('deleted_at', Null)->where('driver_id',$this->driver->id)->get()->count();
             $this->driver_breakdowns = Breakdown::whereYear('date',date('Y'))->where('deleted_at', Null)->where('driver_id',$this->driver->id)->get()->count();
             $this->driver_trips = Trip::whereYear('start_date',date('Y'))->where('deleted_at', Null)->where('driver_id',$this->driver->id)->get()->count();
             $this->driver_recoveries = Recovery::whereYear('date',date('Y'))->where('deleted_at', Null)->where('driver_id',$this->driver->id)->get()->count();
        }
        $this->horse_count = Horse::where('archive',false)->get()->count();
        $this->destinations_count = Destination::all()->count();
        $this->trailer_count = Trailer::where('archive',false)->get()->count();
        $this->vendor_count = Vendor::where('status',true)->get()->count();
        $this->vehicle_count = Vehicle::where('archive',false)->get()->count();
        $this->assignment_count = Assignment::all()->count();
        $this->inspection_count = Checklist::whereYear('date',date('Y'))->get()->count();
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
        $this->inventory_dispatches_count = Dispatch::whereYear('date',date('Y'))->get()->count();
        $this->inventory_purchases_count = Purchase::whereYear('date',date('Y'))->where('department','inventory')->get()->count();
        $this->booking_count = Booking::where('authorization','approved')->whereYear('created_at', date('Y'))->count();
        $this->ticket_count = Ticket::whereYear('created_at', date('Y'))->count();
        $this->workshop_inspection_count = Inspection::whereYear('created_at', date('Y'))->count();
        $this->my_tickets_count = Ticket::with('booking')->whereHas('booking.employees', function ($q) {
                    $q->where('employees.id',$this->employee->id);
                })->whereYear('created_at', date('Y'))->count();
        $this->my_inspections_count = Inspection::with('booking')->whereHas('booking.employees', function ($q) {
                    $q->where('employees.id',$this->employee->id);
                })->whereYear('created_at', date('Y'))->count();
        $this->fuel_supplier_count = Container::all()->count();
        $this->fuel_order_count = Fuel::whereYear('date', date('Y'))->count();
        $this->transport_order_count = TransportOrder::whereYear('created_at', date('Y'))->count();
        $this->leave_count = Leave::whereYear('created_at', date('Y'))->count();
        $this->attendance_count = Attendance::whereMonth('created_at', date('m'))->count();
        $this->recent_employees = Employee::latest()->take('5')->get();
        $this->containers = Container::latest()->get();
        $this->allocations = Allocation::latest()->take('5')->get();
        $this->myallocations = Allocation::where('employee_id', Auth::user()->employee->id)->latest()->take('5')->get();
        $this->petrol_quantity = Container::where('fuel_type','Petrol')->sum('balance');
        $this->diesel_quantity = Container::where('fuel_type','Diesel')->sum('balance');

        $this->year = now()->year;
        $this->loadChart();
    }

    public function updatedYear()
    {
        $this->loadChart();

        // Push updated data to the browser (no full page refresh needed)
        $this->dispatch('drivers-weight-updated',
            data: $this->chartData,
            year: $this->year
        );
    }

    private function loadChart(): void
    {
        $drivers = Driver::query()
            ->with(['employee:id,name,surname']) // adjust if your columns differ
            ->withSum([
                'trips as year_total_weight' => function ($q) {
                    $q->whereYear('start_date', $this->year);   // <-- your trip date column
                }
            ], 'weight')                                  // <-- your weight column
            ->orderByRaw('COALESCE(year_total_weight, 0) DESC')
            ->get();

        $this->chartData = $drivers->map(function ($d) {
            $name = trim(($d->employee->name ?? '') . ' ' . ($d->employee->surname ?? ''));
            $name = $name !== '' ? $name : ($d->name ?? ('Driver #' . $d->id));

            return [$name, (float) ($d->year_total_weight ?? 0)];
        })->values()->all();
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
        $this->income_2026 = Invoice::whereYear('date', '2026')->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');
        $this->expenses_2026 = Bill::whereYear('bill_date', '2026')->where('to_be_paid', True)->where('currency_id', $this->selectedCurrency)->where('authorization','approved')->where('total','!=',Null)->where('total','!=',"")->sum('total');

        return view('livewire.dashboard.index');
    }
}
