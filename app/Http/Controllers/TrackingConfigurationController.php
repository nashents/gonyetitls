<?php

namespace App\Http\Controllers;

use App\Models\TrackingConfiguration;
use App\Http\Requests\StoreTrackingConfigurationRequest;
use App\Http\Requests\UpdateTrackingConfigurationRequest;

class TrackingConfigurationController extends Controller
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
     * @param  \App\Http\Requests\StoreTrackingConfigurationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTrackingConfigurationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TrackingConfiguration  $trackingConfiguration
     * @return \Illuminate\Http\Response
     */
    public function show(TrackingConfiguration $trackingConfiguration)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TrackingConfiguration  $trackingConfiguration
     * @return \Illuminate\Http\Response
     */
    public function edit(TrackingConfiguration $trackingConfiguration)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTrackingConfigurationRequest  $request
     * @param  \App\Models\TrackingConfiguration  $trackingConfiguration
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTrackingConfigurationRequest $request, TrackingConfiguration $trackingConfiguration)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TrackingConfiguration  $trackingConfiguration
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrackingConfiguration $trackingConfiguration)
    {
        //
    }
}
