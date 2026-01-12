<?php

namespace App\Http\Controllers;

use App\Models\ModuleGroup;
use App\Http\Requests\StoreModuleGroupRequest;
use App\Http\Requests\UpdateModuleGroupRequest;

class ModuleGroupController extends Controller
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
     * @param  \App\Http\Requests\StoreModuleGroupRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreModuleGroupRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ModuleGroup  $moduleGroup
     * @return \Illuminate\Http\Response
     */
    public function show(ModuleGroup $moduleGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ModuleGroup  $moduleGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(ModuleGroup $moduleGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateModuleGroupRequest  $request
     * @param  \App\Models\ModuleGroup  $moduleGroup
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateModuleGroupRequest $request, ModuleGroup $moduleGroup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ModuleGroup  $moduleGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(ModuleGroup $moduleGroup)
    {
        //
    }
}
