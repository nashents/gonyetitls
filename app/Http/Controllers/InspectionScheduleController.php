<?php

namespace App\Http\Controllers;

use App\Models\InspectionSchedule;
use App\Http\Requests\StoreInspectionScheduleRequest;
use App\Http\Requests\UpdateInspectionScheduleRequest;

class InspectionScheduleController extends Controller
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
     * @param  \App\Http\Requests\StoreInspectionScheduleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInspectionScheduleRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InspectionSchedule  $inspectionSchedule
     * @return \Illuminate\Http\Response
     */
    public function show(InspectionSchedule $inspectionSchedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InspectionSchedule  $inspectionSchedule
     * @return \Illuminate\Http\Response
     */
    public function edit(InspectionSchedule $inspectionSchedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInspectionScheduleRequest  $request
     * @param  \App\Models\InspectionSchedule  $inspectionSchedule
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInspectionScheduleRequest $request, InspectionSchedule $inspectionSchedule)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InspectionSchedule  $inspectionSchedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(InspectionSchedule $inspectionSchedule)
    {
        //
    }
}
