<?php

namespace App\Http\Controllers;

use App\Models\IncidentInjury;
use App\Http\Requests\StoreIncidentInjuryRequest;
use App\Http\Requests\UpdateIncidentInjuryRequest;

class IncidentInjuryController extends Controller
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
     * @param  \App\Http\Requests\StoreIncidentInjuryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreIncidentInjuryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\IncidentInjury  $incidentInjury
     * @return \Illuminate\Http\Response
     */
    public function show(IncidentInjury $incidentInjury)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\IncidentInjury  $incidentInjury
     * @return \Illuminate\Http\Response
     */
    public function edit(IncidentInjury $incidentInjury)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateIncidentInjuryRequest  $request
     * @param  \App\Models\IncidentInjury  $incidentInjury
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateIncidentInjuryRequest $request, IncidentInjury $incidentInjury)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\IncidentInjury  $incidentInjury
     * @return \Illuminate\Http\Response
     */
    public function destroy(IncidentInjury $incidentInjury)
    {
        //
    }
}
