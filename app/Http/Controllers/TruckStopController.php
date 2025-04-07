<?php

namespace App\Http\Controllers;

use App\Models\TruckStop;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreTruckStopRequest;
use App\Http\Requests\UpdateTruckStopRequest;

class TruckStopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('truck_stops.index');
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
     * @param  \App\Http\Requests\StoreTruckStopRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTruckStopRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TruckStop  $truckStop
     * @return \Illuminate\Http\Response
     */
    public function show(TruckStop $truckStop)
    {
        return view('truck_stops.show')->with('truck_stop',$truckStop);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TruckStop  $truckStop
     * @return \Illuminate\Http\Response
     */
    public function edit(TruckStop $truckStop)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTruckStopRequest  $request
     * @param  \App\Models\TruckStop  $truckStop
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTruckStopRequest $request, TruckStop $truckStop)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TruckStop  $truckStop
     * @return \Illuminate\Http\Response
     */
    public function destroy(TruckStop $truckStop)
    {
        $truckStop->delete();
        Session::flash('success','Truck Stop Deleted Successfully');
        return redirect()->back();
    }
}
