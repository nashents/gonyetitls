<?php

namespace App\Http\Controllers;

use App\Models\Breakage;
use App\Http\Requests\StoreBreakageRequest;
use App\Http\Requests\UpdateBreakageRequest;

class BreakageController extends Controller
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
     * @param  \App\Http\Requests\StoreBreakageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBreakageRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Breakage  $breakage
     * @return \Illuminate\Http\Response
     */
    public function show(Breakage $breakage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Breakage  $breakage
     * @return \Illuminate\Http\Response
     */
    public function edit(Breakage $breakage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBreakageRequest  $request
     * @param  \App\Models\Breakage  $breakage
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBreakageRequest $request, Breakage $breakage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Breakage  $breakage
     * @return \Illuminate\Http\Response
     */
    public function destroy(Breakage $breakage)
    {
        //
    }
}
