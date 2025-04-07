<?php

namespace App\Http\Controllers;

use App\Models\IncidentDamage;
use App\Http\Requests\StoreIncidentDamageRequest;
use App\Http\Requests\UpdateIncidentDamageRequest;

class IncidentDamageController extends Controller
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
     * @param  \App\Http\Requests\StoreIncidentDamageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreIncidentDamageRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\IncidentDamage  $incidentDamage
     * @return \Illuminate\Http\Response
     */
    public function show(IncidentDamage $incidentDamage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\IncidentDamage  $incidentDamage
     * @return \Illuminate\Http\Response
     */
    public function edit(IncidentDamage $incidentDamage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateIncidentDamageRequest  $request
     * @param  \App\Models\IncidentDamage  $incidentDamage
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateIncidentDamageRequest $request, IncidentDamage $incidentDamage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\IncidentDamage  $incidentDamage
     * @return \Illuminate\Http\Response
     */
    public function destroy(IncidentDamage $incidentDamage)
    {
        //
    }
}
