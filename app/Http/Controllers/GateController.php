<?php

namespace App\Http\Controllers;

use App\Models\Gate;
use App\Http\Requests\StoreGateRequest;
use App\Http\Requests\UpdateGateRequest;

class GateController extends Controller
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
     * @param  \App\Http\Requests\StoreGateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreGateRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Gate  $gate
     * @return \Illuminate\Http\Response
     */
    public function show(Gate $gate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Gate  $gate
     * @return \Illuminate\Http\Response
     */
    public function edit(Gate $gate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateGateRequest  $request
     * @param  \App\Models\Gate  $gate
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGateRequest $request, Gate $gate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Gate  $gate
     * @return \Illuminate\Http\Response
     */
    public function destroy(Gate $gate)
    {
        //
    }
}
