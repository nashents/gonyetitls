<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransfer;
use App\Http\Requests\StoreInventoryTransferRequest;
use App\Http\Requests\UpdateInventoryTransferRequest;

class InventoryTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
           public function index()
    {
        return view('transfers.index')->with(
            [
                'department' => 'inventory'
            ]
        );
    }
        public function pending()
    {
        return view('transfers.pending')->with(
            [
                'department' => 'inventory'
            ]
        );
    }
    public function approved()
    {
        return view('transfers.approved')->with(
            [
                'department' => 'inventory'
            ]
        );
    }
    public function rejected()
    {
        return view('transfers.rejected')->with(
            [
                'department' => 'inventory'
            ]
        );
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
     * @param  \App\Http\Requests\StoreInventoryTransferRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInventoryTransferRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InventoryTransfer  $inventoryTransfer
     * @return \Illuminate\Http\Response
     */
    public function show(InventoryTransfer $inventoryTransfer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InventoryTransfer  $inventoryTransfer
     * @return \Illuminate\Http\Response
     */
    public function edit(InventoryTransfer $inventoryTransfer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInventoryTransferRequest  $request
     * @param  \App\Models\InventoryTransfer  $inventoryTransfer
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInventoryTransferRequest $request, InventoryTransfer $inventoryTransfer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InventoryTransfer  $inventoryTransfer
     * @return \Illuminate\Http\Response
     */
    public function destroy(InventoryTransfer $inventoryTransfer)
    {
        //
    }
}
