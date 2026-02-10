<?php

namespace App\Http\Controllers;

use App\Models\WasteCollectionItem;
use App\Http\Requests\StoreWasteCollectionItemRequest;
use App\Http\Requests\UpdateWasteCollectionItemRequest;

class WasteCollectionItemController extends Controller
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
     * @param  \App\Http\Requests\StoreWasteCollectionItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWasteCollectionItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WasteCollectionItem  $wasteCollectionItem
     * @return \Illuminate\Http\Response
     */
    public function show(WasteCollectionItem $wasteCollectionItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WasteCollectionItem  $wasteCollectionItem
     * @return \Illuminate\Http\Response
     */
    public function edit(WasteCollectionItem $wasteCollectionItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateWasteCollectionItemRequest  $request
     * @param  \App\Models\WasteCollectionItem  $wasteCollectionItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWasteCollectionItemRequest $request, WasteCollectionItem $wasteCollectionItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WasteCollectionItem  $wasteCollectionItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(WasteCollectionItem $wasteCollectionItem)
    {
        //
    }
}
