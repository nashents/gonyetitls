<?php

namespace App\Http\Controllers;

use App\Models\InventorySerial;
use App\Http\Requests\StoreInventorySerialRequest;
use App\Http\Requests\UpdateInventorySerialRequest;

class InventorySerialController extends Controller
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
     * @param  \App\Http\Requests\StoreInventorySerialRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInventorySerialRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InventorySerial  $inventorySerial
     * @return \Illuminate\Http\Response
     */
    public function show(InventorySerial $inventorySerial)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InventorySerial  $inventorySerial
     * @return \Illuminate\Http\Response
     */
    public function edit(InventorySerial $inventorySerial)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInventorySerialRequest  $request
     * @param  \App\Models\InventorySerial  $inventorySerial
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInventorySerialRequest $request, InventorySerial $inventorySerial)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InventorySerial  $inventorySerial
     * @return \Illuminate\Http\Response
     */
    public function destroy(InventorySerial $inventorySerial)
    {
        //
    }
}
