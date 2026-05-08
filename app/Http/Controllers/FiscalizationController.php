<?php

namespace App\Http\Controllers;

use App\Models\Fiscalization;
use App\Http\Requests\StoreFiscalizationRequest;
use App\Http\Requests\UpdateFiscalizationRequest;

class FiscalizationController extends Controller
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
     * @param  \App\Http\Requests\StoreFiscalizationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFiscalizationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Fiscalization  $fiscalization
     * @return \Illuminate\Http\Response
     */
    public function show(Fiscalization $fiscalization)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Fiscalization  $fiscalization
     * @return \Illuminate\Http\Response
     */
    public function edit(Fiscalization $fiscalization)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateFiscalizationRequest  $request
     * @param  \App\Models\Fiscalization  $fiscalization
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFiscalizationRequest $request, Fiscalization $fiscalization)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Fiscalization  $fiscalization
     * @return \Illuminate\Http\Response
     */
    public function destroy(Fiscalization $fiscalization)
    {
        //
    }
}
