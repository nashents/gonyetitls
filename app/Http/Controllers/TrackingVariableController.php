<?php

namespace App\Http\Controllers;

use App\Models\TrackingVariable;
use App\Http\Requests\StoreTrackingVariableRequest;
use App\Http\Requests\UpdateTrackingVariableRequest;

class TrackingVariableController extends Controller
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
     * @param  \App\Http\Requests\StoreTrackingVariableRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTrackingVariableRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TrackingVariable  $trackingVariable
     * @return \Illuminate\Http\Response
     */
    public function show(TrackingVariable $trackingVariable)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TrackingVariable  $trackingVariable
     * @return \Illuminate\Http\Response
     */
    public function edit(TrackingVariable $trackingVariable)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTrackingVariableRequest  $request
     * @param  \App\Models\TrackingVariable  $trackingVariable
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTrackingVariableRequest $request, TrackingVariable $trackingVariable)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TrackingVariable  $trackingVariable
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrackingVariable $trackingVariable)
    {
        //
    }
}
