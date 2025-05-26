<?php

namespace App\Http\Controllers;

use App\Models\LeaveTypeEmployee;
use App\Http\Requests\StoreLeaveTypeEmployeeRequest;
use App\Http\Requests\UpdateLeaveTypeEmployeeRequest;

class LeaveTypeEmployeeController extends Controller
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
     * @param  \App\Http\Requests\StoreLeaveTypeEmployeeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLeaveTypeEmployeeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LeaveTypeEmployee  $leaveTypeEmployee
     * @return \Illuminate\Http\Response
     */
    public function show(LeaveTypeEmployee $leaveTypeEmployee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LeaveTypeEmployee  $leaveTypeEmployee
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveTypeEmployee $leaveTypeEmployee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLeaveTypeEmployeeRequest  $request
     * @param  \App\Models\LeaveTypeEmployee  $leaveTypeEmployee
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLeaveTypeEmployeeRequest $request, LeaveTypeEmployee $leaveTypeEmployee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LeaveTypeEmployee  $leaveTypeEmployee
     * @return \Illuminate\Http\Response
     */
    public function destroy(LeaveTypeEmployee $leaveTypeEmployee)
    {
        //
    }
}
