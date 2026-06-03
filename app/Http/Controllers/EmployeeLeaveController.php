<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLeave;
use App\Http\Requests\StoreEmployeeLeaveRequest;
use App\Http\Requests\UpdateEmployeeLeaveRequest;

class EmployeeLeaveController extends Controller
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
     * @param  \App\Http\Requests\StoreEmployeeLeaveRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEmployeeLeaveRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EmployeeLeave  $employeeLeave
     * @return \Illuminate\Http\Response
     */
    public function show(EmployeeLeave $employeeLeave)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EmployeeLeave  $employeeLeave
     * @return \Illuminate\Http\Response
     */
    public function edit(EmployeeLeave $employeeLeave)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateEmployeeLeaveRequest  $request
     * @param  \App\Models\EmployeeLeave  $employeeLeave
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEmployeeLeaveRequest $request, EmployeeLeave $employeeLeave)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmployeeLeave  $employeeLeave
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeLeave $employeeLeave)
    {
        //
    }
}
