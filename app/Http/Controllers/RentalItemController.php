<?php

namespace App\Http\Controllers;

use App\Models\RentalItem;
use App\Http\Requests\StoreRentalItemRequest;
use App\Http\Requests\UpdateRentalItemRequest;

class RentalItemController extends Controller
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
     * @param  \App\Http\Requests\StoreRentalItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRentalItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RentalItem  $rentalItem
     * @return \Illuminate\Http\Response
     */
    public function show(RentalItem $rentalItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RentalItem  $rentalItem
     * @return \Illuminate\Http\Response
     */
    public function edit(RentalItem $rentalItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRentalItemRequest  $request
     * @param  \App\Models\RentalItem  $rentalItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRentalItemRequest $request, RentalItem $rentalItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RentalItem  $rentalItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(RentalItem $rentalItem)
    {
        //
    }
}
