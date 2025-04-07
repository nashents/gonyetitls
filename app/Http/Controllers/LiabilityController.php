<?php

namespace App\Http\Controllers;

use App\Models\Liability;
use App\Http\Requests\StoreLiabilityRequest;
use App\Http\Requests\UpdateLiabilityRequest;

class LiabilityController extends Controller
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
     * @param  \App\Http\Requests\StoreLiabilityRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLiabilityRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Liability  $liability
     * @return \Illuminate\Http\Response
     */
    public function show(Liability $liability)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Liability  $liability
     * @return \Illuminate\Http\Response
     */
    public function edit(Liability $liability)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLiabilityRequest  $request
     * @param  \App\Models\Liability  $liability
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLiabilityRequest $request, Liability $liability)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Liability  $liability
     * @return \Illuminate\Http\Response
     */
    public function destroy(Liability $liability)
    {
        //
    }
}
