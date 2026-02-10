<?php

namespace App\Http\Controllers;

use App\Models\WasteType;
use App\Http\Requests\StoreWasteTypeRequest;
use App\Http\Requests\UpdateWasteTypeRequest;

class WasteTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('waste_types.index');
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
     * @param  \App\Http\Requests\StoreWasteTypeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWasteTypeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WasteType  $wasteType
     * @return \Illuminate\Http\Response
     */
    public function show(WasteType $wasteType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WasteType  $wasteType
     * @return \Illuminate\Http\Response
     */
    public function edit(WasteType $wasteType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateWasteTypeRequest  $request
     * @param  \App\Models\WasteType  $wasteType
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWasteTypeRequest $request, WasteType $wasteType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WasteType  $wasteType
     * @return \Illuminate\Http\Response
     */
    public function destroy(WasteType $wasteType)
    {
        //
    }
}
