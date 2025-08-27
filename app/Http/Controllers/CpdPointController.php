<?php

namespace App\Http\Controllers;

use App\Models\CpdPoint;
use App\Http\Requests\StoreCpdPointRequest;
use App\Http\Requests\UpdateCpdPointRequest;

class CpdPointController extends Controller
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
     * @param  \App\Http\Requests\StoreCpdPointRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCpdPointRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CpdPoint  $cpdPoint
     * @return \Illuminate\Http\Response
     */
    public function show(CpdPoint $cpdPoint)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CpdPoint  $cpdPoint
     * @return \Illuminate\Http\Response
     */
    public function edit(CpdPoint $cpdPoint)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCpdPointRequest  $request
     * @param  \App\Models\CpdPoint  $cpdPoint
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCpdPointRequest $request, CpdPoint $cpdPoint)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CpdPoint  $cpdPoint
     * @return \Illuminate\Http\Response
     */
    public function destroy(CpdPoint $cpdPoint)
    {
        //
    }
}
