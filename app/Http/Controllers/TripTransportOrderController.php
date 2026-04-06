<?php

namespace App\Http\Controllers;

use App\Models\TripTransportOrder;
use App\Http\Requests\StoreTripTransportOrderRequest;
use App\Http\Requests\UpdateTripTransportOrderRequest;

class TripTransportOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('trip_transport_orders.index');
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
     * @param  \App\Http\Requests\StoreTripTransportOrderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTripTransportOrderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripTransportOrder  $tripTransportOrder
     * @return \Illuminate\Http\Response
     */
    public function show(TripTransportOrder $tripTransportOrder)
    {
        return view('trip_transport_orders.show',[
            'trip_transport_order' => $tripTransportOrder
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TripTransportOrder  $tripTransportOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(TripTransportOrder $tripTransportOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTripTransportOrderRequest  $request
     * @param  \App\Models\TripTransportOrder  $tripTransportOrder
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTripTransportOrderRequest $request, TripTransportOrder $tripTransportOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripTransportOrder  $tripTransportOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripTransportOrder $tripTransportOrder)
    {
        //
    }
}
