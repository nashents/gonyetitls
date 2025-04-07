<?php

namespace App\Http\Controllers;


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
use App\Models\Service;
use App\Models\Trailer;
use App\Models\Vehicle;
use App\Models\CashFlow;
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
use Illuminate\Http\Request;
use App\Models\DepartmentHead;
use App\Models\TransportOrder;
use Illuminate\Support\Facades\Auth;
use App\Http\Livewire\Inventories\Requisition;

class DashboardController extends Controller
{
    
    public function thirdParty(){
       

        return view('dashboard.third_party')->with('trips',$trips);
    }


    public function index(){
        if (Auth::user()->category =="company") {
            return view('dashboard.company');
        }else {
            return view('dashboard.index');
        }
    }


}
