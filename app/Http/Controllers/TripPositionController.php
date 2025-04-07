<?php

namespace App\Http\Controllers;

use App\Models\TripPosition;
use App\Http\Requests\StoreTripPositionRequest;
use App\Http\Requests\UpdateTripPositionRequest;

class TripPositionController extends Controller
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
     * @param  \App\Http\Requests\StoreTripPositionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTripPositionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripPosition  $tripPosition
     * @return \Illuminate\Http\Response
     */
    public function show(TripPosition $tripPosition)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TripPosition  $tripPosition
     * @return \Illuminate\Http\Response
     */
    public function edit(TripPosition $tripPosition)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTripPositionRequest  $request
     * @param  \App\Models\TripPosition  $tripPosition
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTripPositionRequest $request, TripPosition $tripPosition)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripPosition  $tripPosition
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripPosition $tripPosition)
    {
        //
    }
}
