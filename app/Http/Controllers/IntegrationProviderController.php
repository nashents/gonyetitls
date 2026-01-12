<?php

namespace App\Http\Controllers;

use App\Models\IntegrationProvider;
use App\Http\Requests\StoreIntegrationProviderRequest;
use App\Http\Requests\UpdateIntegrationProviderRequest;

class IntegrationProviderController extends Controller
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
     * @param  \App\Http\Requests\StoreIntegrationProviderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreIntegrationProviderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\IntegrationProvider  $integrationProvider
     * @return \Illuminate\Http\Response
     */
    public function show(IntegrationProvider $integrationProvider)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\IntegrationProvider  $integrationProvider
     * @return \Illuminate\Http\Response
     */
    public function edit(IntegrationProvider $integrationProvider)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateIntegrationProviderRequest  $request
     * @param  \App\Models\IntegrationProvider  $integrationProvider
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateIntegrationProviderRequest $request, IntegrationProvider $integrationProvider)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\IntegrationProvider  $integrationProvider
     * @return \Illuminate\Http\Response
     */
    public function destroy(IntegrationProvider $integrationProvider)
    {
        //
    }
}
