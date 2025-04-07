<?php

namespace App\Http\Controllers;

use App\Models\InspectionResult;
use App\Http\Requests\StoreInspectionResultRequest;
use App\Http\Requests\UpdateInspectionResultRequest;

class InspectionResultController extends Controller
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
     * @param  \App\Http\Requests\StoreInspectionResultRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInspectionResultRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InspectionResult  $inspectionResult
     * @return \Illuminate\Http\Response
     */
    public function show(InspectionResult $inspectionResult)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InspectionResult  $inspectionResult
     * @return \Illuminate\Http\Response
     */
    public function edit(InspectionResult $inspectionResult)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInspectionResultRequest  $request
     * @param  \App\Models\InspectionResult  $inspectionResult
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInspectionResultRequest $request, InspectionResult $inspectionResult)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InspectionResult  $inspectionResult
     * @return \Illuminate\Http\Response
     */
    public function destroy(InspectionResult $inspectionResult)
    {
        //
    }
}
