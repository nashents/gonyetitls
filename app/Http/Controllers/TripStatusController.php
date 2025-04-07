<?php

namespace App\Http\Controllers;

use App\Models\TripStatus;
use App\Http\Requests\StoreTripStatusRequest;
use App\Http\Requests\UpdateTripStatusRequest;

class TripStatusController extends Controller
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
     * @param  \App\Http\Requests\StoreTripStatusRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTripStatusRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripStatus  $tripStatus
     * @return \Illuminate\Http\Response
     */
    public function show(TripStatus $tripStatus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TripStatus  $tripStatus
     * @return \Illuminate\Http\Response
     */
    public function edit(TripStatus $tripStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTripStatusRequest  $request
     * @param  \App\Models\TripStatus  $tripStatus
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTripStatusRequest $request, TripStatus $tripStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripStatus  $tripStatus
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripStatus $tripStatus)
    {
        //
    }
}
