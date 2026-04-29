<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceSchedule;
use App\Http\Requests\StoreMaintenanceScheduleRequest;
use App\Http\Requests\UpdateMaintenanceScheduleRequest;

class MaintenanceScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('maintenance_schedules.index');
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
     * @param  \App\Http\Requests\StoreMaintenanceScheduleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMaintenanceScheduleRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MaintenanceSchedule  $maintenanceSchedule
     * @return \Illuminate\Http\Response
     */
    public function show(MaintenanceSchedule $maintenanceSchedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MaintenanceSchedule  $maintenanceSchedule
     * @return \Illuminate\Http\Response
     */
    public function edit(MaintenanceSchedule $maintenanceSchedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMaintenanceScheduleRequest  $request
     * @param  \App\Models\MaintenanceSchedule  $maintenanceSchedule
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMaintenanceScheduleRequest $request, MaintenanceSchedule $maintenanceSchedule)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MaintenanceSchedule  $maintenanceSchedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(MaintenanceSchedule $maintenanceSchedule)
    {
        //
    }
}
