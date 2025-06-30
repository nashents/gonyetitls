<?php

namespace App\Http\Controllers;

use App\Models\DispatchItem;
use App\Http\Requests\StoreDispatchItemRequest;
use App\Http\Requests\UpdateDispatchItemRequest;

class DispatchItemController extends Controller
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
     * @param  \App\Http\Requests\StoreDispatchItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDispatchItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DispatchItem  $dispatchItem
     * @return \Illuminate\Http\Response
     */
    public function show(DispatchItem $dispatchItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DispatchItem  $dispatchItem
     * @return \Illuminate\Http\Response
     */
    public function edit(DispatchItem $dispatchItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDispatchItemRequest  $request
     * @param  \App\Models\DispatchItem  $dispatchItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDispatchItemRequest $request, DispatchItem $dispatchItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DispatchItem  $dispatchItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(DispatchItem $dispatchItem)
    {
        //
    }
}
