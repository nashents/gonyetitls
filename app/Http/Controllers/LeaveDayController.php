<?php

namespace App\Http\Controllers;

use App\Models\LeaveDay;
use App\Http\Requests\StoreLeaveDayRequest;
use App\Http\Requests\UpdateLeaveDayRequest;

class LeaveDayController extends Controller
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
     * @param  \App\Http\Requests\StoreLeaveDayRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLeaveDayRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LeaveDay  $leaveDay
     * @return \Illuminate\Http\Response
     */
    public function show(LeaveDay $leaveDay)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LeaveDay  $leaveDay
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveDay $leaveDay)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLeaveDayRequest  $request
     * @param  \App\Models\LeaveDay  $leaveDay
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLeaveDayRequest $request, LeaveDay $leaveDay)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LeaveDay  $leaveDay
     * @return \Illuminate\Http\Response
     */
    public function destroy(LeaveDay $leaveDay)
    {
        //
    }
}
