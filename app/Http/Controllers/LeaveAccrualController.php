<?php

namespace App\Http\Controllers;

use App\Models\LeaveAccrual;
use App\Http\Requests\StoreLeaveAccrualRequest;
use App\Http\Requests\UpdateLeaveAccrualRequest;

class LeaveAccrualController extends Controller
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
     * @param  \App\Http\Requests\StoreLeaveAccrualRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLeaveAccrualRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LeaveAccrual  $leaveAccrual
     * @return \Illuminate\Http\Response
     */
    public function show(LeaveAccrual $leaveAccrual)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LeaveAccrual  $leaveAccrual
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveAccrual $leaveAccrual)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLeaveAccrualRequest  $request
     * @param  \App\Models\LeaveAccrual  $leaveAccrual
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLeaveAccrualRequest $request, LeaveAccrual $leaveAccrual)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LeaveAccrual  $leaveAccrual
     * @return \Illuminate\Http\Response
     */
    public function destroy(LeaveAccrual $leaveAccrual)
    {
        //
    }
}
