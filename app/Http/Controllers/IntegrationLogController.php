<?php

namespace App\Http\Controllers;

use App\Models\IntegrationLog;
use App\Http\Requests\StoreIntegrationLogRequest;
use App\Http\Requests\UpdateIntegrationLogRequest;

class IntegrationLogController extends Controller
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
     * @param  \App\Http\Requests\StoreIntegrationLogRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreIntegrationLogRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\IntegrationLog  $integrationLog
     * @return \Illuminate\Http\Response
     */
    public function show(IntegrationLog $integrationLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\IntegrationLog  $integrationLog
     * @return \Illuminate\Http\Response
     */
    public function edit(IntegrationLog $integrationLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateIntegrationLogRequest  $request
     * @param  \App\Models\IntegrationLog  $integrationLog
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateIntegrationLogRequest $request, IntegrationLog $integrationLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\IntegrationLog  $integrationLog
     * @return \Illuminate\Http\Response
     */
    public function destroy(IntegrationLog $integrationLog)
    {
        //
    }
}
