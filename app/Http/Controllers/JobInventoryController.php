<?php

namespace App\Http\Controllers;

use App\Models\JobInventory;
use App\Http\Requests\StoreJobInventoryRequest;
use App\Http\Requests\UpdateJobInventoryRequest;

class JobInventoryController extends Controller
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
     * @param  \App\Http\Requests\StoreJobInventoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreJobInventoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\JobInventory  $jobInventory
     * @return \Illuminate\Http\Response
     */
    public function show(JobInventory $jobInventory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JobInventory  $jobInventory
     * @return \Illuminate\Http\Response
     */
    public function edit(JobInventory $jobInventory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateJobInventoryRequest  $request
     * @param  \App\Models\JobInventory  $jobInventory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateJobInventoryRequest $request, JobInventory $jobInventory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JobInventory  $jobInventory
     * @return \Illuminate\Http\Response
     */
    public function destroy(JobInventory $jobInventory)
    {
        //
    }
}
