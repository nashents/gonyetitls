<?php

namespace App\Http\Controllers;

use App\Models\InventoryDispatch;
use App\Http\Requests\StoreInventoryDispatchRequest;
use App\Http\Requests\UpdateInventoryDispatchRequest;

class InventoryDispatchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('inventory_dispatches.index');
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
     * @param  \App\Http\Requests\StoreInventoryDispatchRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInventoryDispatchRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InventoryDispatch  $inventoryDispatch
     * @return \Illuminate\Http\Response
     */
    public function show(InventoryDispatch $inventoryDispatch)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InventoryDispatch  $inventoryDispatch
     * @return \Illuminate\Http\Response
     */
    public function edit(InventoryDispatch $inventoryDispatch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInventoryDispatchRequest  $request
     * @param  \App\Models\InventoryDispatch  $inventoryDispatch
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInventoryDispatchRequest $request, InventoryDispatch $inventoryDispatch)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InventoryDispatch  $inventoryDispatch
     * @return \Illuminate\Http\Response
     */
    public function destroy(InventoryDispatch $inventoryDispatch)
    {
        //
    }
}
