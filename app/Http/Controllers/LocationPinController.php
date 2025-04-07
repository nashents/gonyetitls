<?php

namespace App\Http\Controllers;

use App\Models\LocationPin;
use App\Http\Requests\StoreLocationPinRequest;
use App\Http\Requests\UpdateLocationPinRequest;

class LocationPinController extends Controller
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
     * @param  \App\Http\Requests\StoreLocationPinRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLocationPinRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LocationPin  $locationPin
     * @return \Illuminate\Http\Response
     */
    public function show(LocationPin $locationPin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LocationPin  $locationPin
     * @return \Illuminate\Http\Response
     */
    public function edit(LocationPin $locationPin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLocationPinRequest  $request
     * @param  \App\Models\LocationPin  $locationPin
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLocationPinRequest $request, LocationPin $locationPin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LocationPin  $locationPin
     * @return \Illuminate\Http\Response
     */
    public function destroy(LocationPin $locationPin)
    {
        //
    }
}
