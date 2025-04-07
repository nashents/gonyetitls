<?php

namespace App\Http\Controllers;

use App\Models\InventoryRequisition;
use App\Http\Requests\StoreInventoryRequisitionRequest;
use App\Http\Requests\UpdateInventoryRequisitionRequest;

class InventoryRequisitionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('inventory_requisitions.index');
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
     * @param  \App\Http\Requests\StoreInventoryRequisitionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInventoryRequisitionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InventoryRequisition  $inventoryRequisition
     * @return \Illuminate\Http\Response
     */
    public function show(InventoryRequisition $inventoryRequisition)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InventoryRequisition  $inventoryRequisition
     * @return \Illuminate\Http\Response
     */
    public function edit(InventoryRequisition $inventoryRequisition)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInventoryRequisitionRequest  $request
     * @param  \App\Models\InventoryRequisition  $inventoryRequisition
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInventoryRequisitionRequest $request, InventoryRequisition $inventoryRequisition)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InventoryRequisition  $inventoryRequisition
     * @return \Illuminate\Http\Response
     */
    public function destroy(InventoryRequisition $inventoryRequisition)
    {
        //
    }
}
