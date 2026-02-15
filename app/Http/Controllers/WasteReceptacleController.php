<?php

namespace App\Http\Controllers;

use App\Models\WasteReceptacle;
use App\Http\Requests\StoreWasteReceptacleRequest;
use App\Http\Requests\UpdateWasteReceptacleRequest;

class WasteReceptacleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         return view('waste_receptacles.index');
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
     * @param  \App\Http\Requests\StoreWasteReceptacleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWasteReceptacleRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WasteReceptacle  $wasteReceptacle
     * @return \Illuminate\Http\Response
     */
    public function show(WasteReceptacle $wasteReceptacle)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WasteReceptacle  $wasteReceptacle
     * @return \Illuminate\Http\Response
     */
    public function edit(WasteReceptacle $wasteReceptacle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateWasteReceptacleRequest  $request
     * @param  \App\Models\WasteReceptacle  $wasteReceptacle
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWasteReceptacleRequest $request, WasteReceptacle $wasteReceptacle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WasteReceptacle  $wasteReceptacle
     * @return \Illuminate\Http\Response
     */
    public function destroy(WasteReceptacle $wasteReceptacle)
    {
        //
    }
}
