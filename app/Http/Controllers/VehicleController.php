<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('vehicles.index');
    }

    public function archived()
    {
        return view('vehicles.archived');
    }
    public function mileage()
    {
        return view('vehicles.mileage');
    }
    public function archive($id){
        $vehicle = Vehicle::find($id);
        $vehicle->archive = 1;
        $vehicle->service = 1;
        $vehicle->status = 0;
        $vehicle->update();
        Session::flash('success','Vehicle Archived Successfully!!');
        return redirect(route('vehicles.archived'));
    }
    public function age()
    {
        return view('vehicles.age');
    }
    public function manage()
    {
        return view('vehicles.manage');
    }

    public function reports(){
        return view('vehicles.reports');
    }

    public function deactivate(Vehicle $vehicle){
        // $vehicle = Vehicle::find($id);
        $vehicle->status = 0 ;
        $vehicle->update();
        Session::flash('success','Vehicle Successfully Deactivated');
        return redirect(route('vehicles.index'));
    }

    public function activate(Vehicle $vehicle){
        // $vehicle = Vehicle::find($id);
        $vehicle->status = 1 ;
        $vehicle->update();
        Session::flash('success','Vehicle Successfully Activated');
        return redirect(route('vehicles.index'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('vehicles.create');
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
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function show(Vehicle $vehicle)
    {

        $tyre_assignments = $vehicle->tyre_assignments;
        $usages = $vehicle->fuels;
        $total_usage = $vehicle->fuels->sum('quantity');
        $cashflows = $vehicle->cash_flows;
        $documents = $vehicle->vehicle_documents;
        $images = $vehicle->vehicle_images;
        $fitnesses = $vehicle->fitnesses;
        return view('vehicles.show')->with([
            'vehicle' => $vehicle,
            'tyre_assignments' => $tyre_assignments,
            'cashflows' => $cashflows,
            'documents' => $documents,
            'images' => $images,
            'fitnesses' => $fitnesses,
            'usages' => $usages,
            'total_usage' => $total_usage,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function edit(Vehicle $vehicle)
    {
        return view('vehicles.edit')->with('vehicle',$vehicle);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        Session::flash('success','Vehicle Deleted Successfully!!');
        return redirect()->back();
    }
}
