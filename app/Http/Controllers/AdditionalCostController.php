<?php

namespace App\Http\Controllers;

use App\Models\AdditionalCost;
use App\Http\Requests\StoreAdditionalCostRequest;
use App\Http\Requests\UpdateAdditionalCostRequest;

class AdditionalCostController extends Controller
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
     * @param  \App\Http\Requests\StoreAdditionalCostRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAdditionalCostRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AdditionalCost  $additionalCost
     * @return \Illuminate\Http\Response
     */
    public function show(AdditionalCost $additionalCost)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AdditionalCost  $additionalCost
     * @return \Illuminate\Http\Response
     */
    public function edit(AdditionalCost $additionalCost)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAdditionalCostRequest  $request
     * @param  \App\Models\AdditionalCost  $additionalCost
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAdditionalCostRequest $request, AdditionalCost $additionalCost)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AdditionalCost  $additionalCost
     * @return \Illuminate\Http\Response
     */
    public function destroy(AdditionalCost $additionalCost)
    {
        //
    }
}
