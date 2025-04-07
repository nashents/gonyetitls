<?php

namespace App\Http\Controllers;

use App\Models\ConsumptionLog;
use App\Http\Requests\StoreConsumptionLogRequest;
use App\Http\Requests\UpdateConsumptionLogRequest;

class ConsumptionLogController extends Controller
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
     * @param  \App\Http\Requests\StoreConsumptionLogRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreConsumptionLogRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ConsumptionLog  $consumptionLog
     * @return \Illuminate\Http\Response
     */
    public function show(ConsumptionLog $consumptionLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ConsumptionLog  $consumptionLog
     * @return \Illuminate\Http\Response
     */
    public function edit(ConsumptionLog $consumptionLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateConsumptionLogRequest  $request
     * @param  \App\Models\ConsumptionLog  $consumptionLog
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateConsumptionLogRequest $request, ConsumptionLog $consumptionLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ConsumptionLog  $consumptionLog
     * @return \Illuminate\Http\Response
     */
    public function destroy(ConsumptionLog $consumptionLog)
    {
        //
    }
}
