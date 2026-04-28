<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceCategory;
use App\Http\Requests\StoreMaintenanceCategoryRequest;
use App\Http\Requests\UpdateMaintenanceCategoryRequest;

class MaintenanceCategoryController extends Controller
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
     * @param  \App\Http\Requests\StoreMaintenanceCategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMaintenanceCategoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MaintenanceCategory  $maintenanceCategory
     * @return \Illuminate\Http\Response
     */
    public function show(MaintenanceCategory $maintenanceCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MaintenanceCategory  $maintenanceCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(MaintenanceCategory $maintenanceCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMaintenanceCategoryRequest  $request
     * @param  \App\Models\MaintenanceCategory  $maintenanceCategory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMaintenanceCategoryRequest $request, MaintenanceCategory $maintenanceCategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MaintenanceCategory  $maintenanceCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(MaintenanceCategory $maintenanceCategory)
    {
        //
    }
}
