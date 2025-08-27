<?php

namespace App\Http\Controllers;

use App\Models\EmployeePosition;
use App\Http\Requests\StoreEmployeePositionRequest;
use App\Http\Requests\UpdateEmployeePositionRequest;

class EmployeePositionController extends Controller
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
     * @param  \App\Http\Requests\StoreEmployeePositionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEmployeePositionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EmployeePosition  $employeePosition
     * @return \Illuminate\Http\Response
     */
    public function show(EmployeePosition $employeePosition)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EmployeePosition  $employeePosition
     * @return \Illuminate\Http\Response
     */
    public function edit(EmployeePosition $employeePosition)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateEmployeePositionRequest  $request
     * @param  \App\Models\EmployeePosition  $employeePosition
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEmployeePositionRequest $request, EmployeePosition $employeePosition)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmployeePosition  $employeePosition
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeePosition $employeePosition)
    {
        //
    }
}
