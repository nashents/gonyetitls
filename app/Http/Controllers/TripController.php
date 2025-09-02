<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Currency;
use App\Models\Destination;
use App\Models\TripExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TripController extends Controller
{

   

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('trips.index');
    }
    public function thirdParty()
    {
        return view('third_parties.index');
    }
    public function thirdPartyShow($id)
    {
        $trip = Trip::find($id);
        return view('third_parties.show')->with('trip',$trip);
    }

    public function orders()
    {
        return view('trips.orders');
    }
    public function pending()
    {
        return view('trips.pending');
    }
    public function approved()
    {
        return view('trips.approved');
    }
    public function rejected()
    {
        return view('trips.rejected');
    }
    public function reports(){
        return view('trips.reports');
    }
   

    public function deleted(){
        return view('trips.deleted');
    }

    public function summary($trip_filter = null){
        $company = Auth::user()->employee->company;
            $from = null;
            $to = null;
            $search = null;
        return view('trips.summary')->with([
            'from' => $from,
            'to' => $to,
            'search' => $search,
            'company' => $company,
            'trip_filter' => $trip_filter,
           
          ]);
    }
    public function allSummary($from = null, $to = null, $search = null, $trip_filter = null){
        $company = Auth::user()->employee->company;
        return view('trips.summary')->with([
            'from' => $from,
            'to' => $to,
            'search' => $search,
            'company' => $company,
            'trip_filter' => $trip_filter,
           
          ]);
    }
    public function rangeSummary($from = null, $to = null, $trip_filter = null){
        $company = Auth::user()->employee->company;
        $search = null;
        return view('trips.summary')->with([
            'from' => $from,
            'to' => $to,
            'search' => $search,
            'company' => $company,
            'trip_filter' => $trip_filter,
           
          ]);
    }

    public function searchSummary($search = null, $trip_filter = null){
        $company = Auth::user()->employee->company;
        $from = null;
        $to = null;
        return view('trips.summary')->with([
            'from' => $from,
            'to' => $to,
            'search' => $search,
            'company' => $company,
            'trip_filter' => $trip_filter,
           
          ]);
    }

    public function summaryPrint($trip_filter = null){
        $company = Auth::user()->employee->company;
            $from = null;
            $to = null;
            $search = null;
        return view('trips.summary_print')->with([
            'from' => $from,
            'to' => $to,
            'search' => $search,
            'company' => $company,
            'trip_filter' => $trip_filter,
           
          ]);
    }
    public function allSummaryPrint($from = null, $to = null, $search = null, $trip_filter = null){
        $company = Auth::user()->employee->company;
        return view('trips.summary_print')->with([
            'from' => $from,
            'to' => $to,
            'search' => $search,
            'company' => $company,
            'trip_filter' => $trip_filter,
           
          ]);
    }
    public function rangeSummaryPrint($from = null, $to = null, $trip_filter = null){
        $company = Auth::user()->employee->company;
        $search = null;
        return view('trips.summary_print')->with([
            'from' => $from,
            'to' => $to,
            'search' => $search,
            'company' => $company,
            'trip_filter' => $trip_filter,
           
          ]);
    }

    public function searchSummaryPrint($search = null, $trip_filter = null){
        $company = Auth::user()->employee->company;
        $from = null;
        $to = null;
        return view('trips.summary_print')->with([
            'from' => $from,
            'to' => $to,
            'search' => $search,
            'company' => $company,
            'trip_filter' => $trip_filter,
           
          ]);
    }
  

    public function manifest(Trip $trip){
        $company = Auth::user()->employee->company;
        return view('trips.manifest')->with([
            'trip' => $trip,
            'company' => $company,
          ]);
    }
    
    public function preview(Trip $trip){
        if (isset(Auth::user()->employee->company)) {
            $company = Auth::user()->employee->company;
        }elseif (isset(Auth::user()->company)) {
          $company =  Auth::user()->company;
        }
        $cargos = $trip->cargos;
        $customer = $trip->customer;
        return view('trips.preview')->with([
            'trip' => $trip,
            'company' => $company,
            'cargos' => $cargos,
            'customer' => $customer,
          ]);
    }


    public function print(Trip $trip){

        if (isset(Auth::user()->employee->company)) {
            $company = Auth::user()->employee->company;
        }elseif (isset(Auth::user()->company)) {
          $company =  Auth::user()->company;
        }
        $cargos = $trip->cargos;
        $customer = $trip->customer;
        return view('trips.print')->with([
            'trip' => $trip,
            'company' => $company,
            'cargos' => $cargos,
            'customer' => $customer,
          ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('trips.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function show(Trip $trip)
    {
        return view('trips.show')->with([
            'trip' => $trip,
           
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function edit(Trip $trip)
    {
        return view('trips.edit')->with('trip',$trip);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Trip $trip)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function destroy(Trip $trip)
    {

        $horse = $trip->horse;
        $trailers = $trip->trailers;
        $driver = $trip->driver;
        $vehicle = $trip->vehicle;
        $gate_pass = $trip->gate_pass;

        if (isset($gate_pass)) {
            $gate_pass->delete();
        }
        
        if (isset($vehicle)) {
            $vehicle->status = 1;
            $vehicle->update();
        }
        
        if (isset($horse)) {
            $horse->status = 1;
            $horse->update();
        }

        if (isset($driver)) {
            $driver->status = 1;
            $driver->update();
        }

        if (isset($trailers)) {
            foreach ($trailers as $trailer) {
                $trailer->status = 1;
                $trailer->update();
            }
        }

       
        $fuels = $trip->fuels;
        $delivery_note = $trip->delivery_note;
        $cash_flows = $trip->cash_flows;
        $expenses = $trip->trip_expenses;
        $bills = $trip->bills;
        if (isset($transportation_order)) {
            $transportation_order->delete();
        }
        if (isset($fuels)) {
            foreach ($fuels as $fuel) {
                $fuel->delete();
            }
           
        }
        if (isset($delivery_note)) {
            $delivery_note->delete();
        }
        if (isset($cash_flows)) {
            if ($cash_flows->count()>0) {
               foreach ($cash_flows as $cash_flow) {
               $cash_flow->delete();
               }
            }
        }
        if (isset($bills)) {
            if ($bills->count()>0) {
               foreach ($bills as $bill) {
                $bill_expenses = $bill->bill_expenses;
                if (isset($bill_expenses)) {
                  foreach ($bill_expenses as $expense) {
                        $expense->delete();
                  }
                }
               $bill->delete();
               }
            }
        }

        if (isset($expenses)) {
            if ($expenses->count()>0) {
               foreach ($expenses as $expense) {
               $expense->delete();
               }
            }
        }

        $trip->delete();
        Session::flash('success','Trip Deleted Successfully');
        return redirect()->back();
    }
}
