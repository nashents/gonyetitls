<?php

namespace App\Http\Controllers;

use App\Models\WasteSource;
use App\Http\Requests\StoreWasteSourceRequest;
use App\Http\Requests\UpdateWasteSourceRequest;

class WasteSourceController extends Controller
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
     * @param  \App\Http\Requests\StoreWasteSourceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWasteSourceRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WasteSource  $wasteSource
     * @return \Illuminate\Http\Response
     */
    public function show(WasteSource $wasteSource)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WasteSource  $wasteSource
     * @return \Illuminate\Http\Response
     */
    public function edit(WasteSource $wasteSource)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateWasteSourceRequest  $request
     * @param  \App\Models\WasteSource  $wasteSource
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWasteSourceRequest $request, WasteSource $wasteSource)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WasteSource  $wasteSource
     * @return \Illuminate\Http\Response
     */
    public function destroy(WasteSource $wasteSource)
    {
        //
    }
}
