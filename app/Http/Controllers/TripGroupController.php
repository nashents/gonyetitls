<?php

namespace App\Http\Controllers;

use App\Models\TripGroup;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreTripGroupRequest;
use App\Http\Requests\UpdateTripGroupRequest;

class TripGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('trip_groups.index');
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
     * @param  \App\Http\Requests\StoreTripGroupRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTripGroupRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripGroup  $tripGroup
     * @return \Illuminate\Http\Response
     */
    public function show(TripGroup $tripGroup)
    {
        return view('trip_groups.show')->with('trip_group',$tripGroup);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TripGroup  $tripGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(TripGroup $tripGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTripGroupRequest  $request
     * @param  \App\Models\TripGroup  $tripGroup
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTripGroupRequest $request, TripGroup $tripGroup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripGroup  $tripGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripGroup $tripGroup)
    {
        $trips = $tripGroup->trips;
        if (isset($trips)) {
            foreach ($trips as $trip) {
                $trip->trip_group_id = Null;
                $trip->update();
            }
        }
        $tripGroup->delete();
        Session::flash('success','Tracking Group Deleted Successfully!!');
        return redirect()->back();
    }
}
