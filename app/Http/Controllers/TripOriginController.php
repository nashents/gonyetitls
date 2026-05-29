<?php

namespace App\Http\Controllers;

use App\Models\TripOrigin;
use App\Http\Requests\StoreTripOriginRequest;
use App\Http\Requests\UpdateTripOriginRequest;

class TripOriginController extends Controller
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
     * @param  \App\Http\Requests\StoreTripOriginRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTripOriginRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripOrigin  $tripOrigin
     * @return \Illuminate\Http\Response
     */
    public function show(TripOrigin $tripOrigin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TripOrigin  $tripOrigin
     * @return \Illuminate\Http\Response
     */
    public function edit(TripOrigin $tripOrigin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTripOriginRequest  $request
     * @param  \App\Models\TripOrigin  $tripOrigin
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTripOriginRequest $request, TripOrigin $tripOrigin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripOrigin  $tripOrigin
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripOrigin $tripOrigin)
    {
        //
    }
}
