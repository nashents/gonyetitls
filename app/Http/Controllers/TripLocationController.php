<?php

namespace App\Http\Controllers;

use App\Models\TripLocation;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreTripLocationRequest;
use App\Http\Requests\UpdateTripLocationRequest;

class TripLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoreTripLocationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTripLocationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripLocation  $tripLocation
     * @return \Illuminate\Http\Response
     */
    public function show(TripLocation $tripLocation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TripLocation  $tripLocation
     * @return \Illuminate\Http\Response
     */
    public function edit(TripLocation $tripLocation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTripLocationRequest  $request
     * @param  \App\Models\TripLocation  $tripLocation
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTripLocationRequest $request, TripLocation $tripLocation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripLocation  $tripLocation
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripLocation $tripLocation)
    {
        $tripLocation->delete();
        Session::flash('success','Location Updated Deleted Successfully');
        return redirect()->back();
    }
}
