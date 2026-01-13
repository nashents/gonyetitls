<?php

namespace App\Http\Controllers;

use App\Models\RentalRate;
use App\Http\Requests\StoreRentalRateRequest;
use App\Http\Requests\UpdateRentalRateRequest;

class RentalRateController extends Controller
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
     * @param  \App\Http\Requests\StoreRentalRateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRentalRateRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RentalRate  $rentalRate
     * @return \Illuminate\Http\Response
     */
    public function show(RentalRate $rentalRate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RentalRate  $rentalRate
     * @return \Illuminate\Http\Response
     */
    public function edit(RentalRate $rentalRate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRentalRateRequest  $request
     * @param  \App\Models\RentalRate  $rentalRate
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRentalRateRequest $request, RentalRate $rentalRate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RentalRate  $rentalRate
     * @return \Illuminate\Http\Response
     */
    public function destroy(RentalRate $rentalRate)
    {
        //
    }
}
