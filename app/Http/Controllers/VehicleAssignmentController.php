<?php

namespace App\Http\Controllers;

use App\Models\VehicleAssignment;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreVehicleAssignmentRequest;
use App\Http\Requests\UpdateVehicleAssignmentRequest;

class VehicleAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('vehicle_assignments.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreVehicleAssignmentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreVehicleAssignmentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\VehicleAssignment  $vehicleAssignment
     * @return \Illuminate\Http\Response
     */
    public function show(VehicleAssignment $vehicleAssignment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VehicleAssignment  $vehicleAssignment
     * @return \Illuminate\Http\Response
     */
    public function edit(VehicleAssignment $vehicleAssignment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateVehicleAssignmentRequest  $request
     * @param  \App\Models\VehicleAssignment  $vehicleAssignment
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateVehicleAssignmentRequest $request, VehicleAssignment $vehicleAssignment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VehicleAssignment  $vehicleAssignment
     * @return \Illuminate\Http\Response
     */
    public function destroy(VehicleAssignment $vehicleAssignment)
    {
        $vehicleAssignment->delete();
        Session::flash('success','Employee Vehicle Assignment Deleted Successfully!!');
        return redirect()->back();
    }
}
