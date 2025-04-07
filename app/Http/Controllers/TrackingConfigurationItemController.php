<?php

namespace App\Http\Controllers;

use App\Models\TrackingConfigurationItem;
use App\Http\Requests\StoreTrackingConfigurationItemRequest;
use App\Http\Requests\UpdateTrackingConfigurationItemRequest;

class TrackingConfigurationItemController extends Controller
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
     * @param  \App\Http\Requests\StoreTrackingConfigurationItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTrackingConfigurationItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TrackingConfigurationItem  $trackingConfigurationItem
     * @return \Illuminate\Http\Response
     */
    public function show(TrackingConfigurationItem $trackingConfigurationItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TrackingConfigurationItem  $trackingConfigurationItem
     * @return \Illuminate\Http\Response
     */
    public function edit(TrackingConfigurationItem $trackingConfigurationItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTrackingConfigurationItemRequest  $request
     * @param  \App\Models\TrackingConfigurationItem  $trackingConfigurationItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTrackingConfigurationItemRequest $request, TrackingConfigurationItem $trackingConfigurationItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TrackingConfigurationItem  $trackingConfigurationItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrackingConfigurationItem $trackingConfigurationItem)
    {
        //
    }
}
