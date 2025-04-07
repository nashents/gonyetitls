<?php

namespace App\Http\Controllers;

use App\Models\VehicleGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class VehicleGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('vehicle_groups.index');
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
     * @param  \App\Models\VehicleGroup  $vehicleGroup
     * @return \Illuminate\Http\Response
     */
    public function show(VehicleGroup $vehicleGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VehicleGroup  $vehicleGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(VehicleGroup $vehicleGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VehicleGroup  $vehicleGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VehicleGroup $vehicleGroup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VehicleGroup  $vehicleGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(VehicleGroup $vehicleGroup)
    {
        $vehicleGroup->delete();
        Session::flash('success','Vehicle Group Deleted Successfully!!');
        return redirect()->back();
    }
}
