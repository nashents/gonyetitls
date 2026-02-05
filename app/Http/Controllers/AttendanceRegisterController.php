<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRegister;
use App\Http\Requests\StoreAttendanceRegisterRequest;
use App\Http\Requests\UpdateAttendanceRegisterRequest;

class AttendanceRegisterController extends Controller
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
     * @param  \App\Http\Requests\StoreAttendanceRegisterRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAttendanceRegisterRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AttendanceRegister  $attendanceRegister
     * @return \Illuminate\Http\Response
     */
    public function show(AttendanceRegister $attendanceRegister)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AttendanceRegister  $attendanceRegister
     * @return \Illuminate\Http\Response
     */
    public function edit(AttendanceRegister $attendanceRegister)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAttendanceRegisterRequest  $request
     * @param  \App\Models\AttendanceRegister  $attendanceRegister
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAttendanceRegisterRequest $request, AttendanceRegister $attendanceRegister)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AttendanceRegister  $attendanceRegister
     * @return \Illuminate\Http\Response
     */
    public function destroy(AttendanceRegister $attendanceRegister)
    {
        //
    }
}
