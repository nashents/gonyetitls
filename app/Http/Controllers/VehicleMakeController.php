<?php

namespace App\Http\Controllers;

use App\Models\VehicleMake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class VehicleMakeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('vehicle_makes.index');
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
     * @param  \App\Models\VehicleMake  $vehicleMake
     * @return \Illuminate\Http\Response
     */
    public function show(VehicleMake $vehicleMake)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VehicleMake  $vehicleMake
     * @return \Illuminate\Http\Response
     */
    public function edit(VehicleMake $vehicleMake)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VehicleMake  $vehicleMake
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VehicleMake $vehicleMake)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VehicleMake  $vehicleMake
     * @return \Illuminate\Http\Response
     */
    public function destroy(VehicleMake $vehicleMake)
    {
        foreach ($vehicleMake->vehicle_models as $model) {
            $model->delete();
        }
        $vehicleMake->delete();
        Session::flash('success','Vehicle Make & Models Deleted Successfully!!');
        return redirect()->back();
    }
}
