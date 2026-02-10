<?php

namespace App\Http\Controllers;

use App\Models\WasteDisposalItem;
use App\Http\Requests\StoreWasteDisposalItemRequest;
use App\Http\Requests\UpdateWasteDisposalItemRequest;

class WasteDisposalItemController extends Controller
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
     * @param  \App\Http\Requests\StoreWasteDisposalItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWasteDisposalItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WasteDisposalItem  $wasteDisposalItem
     * @return \Illuminate\Http\Response
     */
    public function show(WasteDisposalItem $wasteDisposalItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WasteDisposalItem  $wasteDisposalItem
     * @return \Illuminate\Http\Response
     */
    public function edit(WasteDisposalItem $wasteDisposalItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateWasteDisposalItemRequest  $request
     * @param  \App\Models\WasteDisposalItem  $wasteDisposalItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWasteDisposalItemRequest $request, WasteDisposalItem $wasteDisposalItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WasteDisposalItem  $wasteDisposalItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(WasteDisposalItem $wasteDisposalItem)
    {
        //
    }
}
