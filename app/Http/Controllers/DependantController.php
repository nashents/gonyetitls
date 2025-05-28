<?php

namespace App\Http\Controllers;

use App\Models\Dependant;
use App\Http\Requests\StoreDependantRequest;
use App\Http\Requests\UpdateDependantRequest;

class DependantController extends Controller
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
     * @param  \App\Http\Requests\StoreDependantRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDependantRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Dependant  $dependant
     * @return \Illuminate\Http\Response
     */
    public function show(Dependant $dependant)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Dependant  $dependant
     * @return \Illuminate\Http\Response
     */
    public function edit(Dependant $dependant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDependantRequest  $request
     * @param  \App\Models\Dependant  $dependant
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDependantRequest $request, Dependant $dependant)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Dependant  $dependant
     * @return \Illuminate\Http\Response
     */
    public function destroy(Dependant $dependant)
    {
        //
    }
}
