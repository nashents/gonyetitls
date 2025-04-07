<?php

namespace App\Http\Controllers;

use App\Models\ModuleCategory;
use App\Http\Requests\StoreModuleCategoryRequest;
use App\Http\Requests\UpdateModuleCategoryRequest;

class ModuleCategoryController extends Controller
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
     * @param  \App\Http\Requests\StoreModuleCategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreModuleCategoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ModuleCategory  $moduleCategory
     * @return \Illuminate\Http\Response
     */
    public function show(ModuleCategory $moduleCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ModuleCategory  $moduleCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(ModuleCategory $moduleCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateModuleCategoryRequest  $request
     * @param  \App\Models\ModuleCategory  $moduleCategory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateModuleCategoryRequest $request, ModuleCategory $moduleCategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ModuleCategory  $moduleCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(ModuleCategory $moduleCategory)
    {
        //
    }
}
