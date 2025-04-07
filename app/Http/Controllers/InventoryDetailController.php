<?php

namespace App\Http\Controllers;

use App\Models\InventoryDetail;
use App\Http\Requests\StoreInventoryDetailRequest;
use App\Http\Requests\UpdateInventoryDetailRequest;

class InventoryDetailController extends Controller
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
     * @param  \App\Http\Requests\StoreInventoryDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInventoryDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InventoryDetail  $inventoryDetail
     * @return \Illuminate\Http\Response
     */
    public function show(InventoryDetail $inventoryDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InventoryDetail  $inventoryDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(InventoryDetail $inventoryDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInventoryDetailRequest  $request
     * @param  \App\Models\InventoryDetail  $inventoryDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInventoryDetailRequest $request, InventoryDetail $inventoryDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InventoryDetail  $inventoryDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(InventoryDetail $inventoryDetail)
    {
        //
    }
}
