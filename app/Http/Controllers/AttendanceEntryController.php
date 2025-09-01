<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEntry;
use App\Http\Requests\StoreAttendanceEntryRequest;
use App\Http\Requests\UpdateAttendanceEntryRequest;

class AttendanceEntryController extends Controller
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
     * @param  \App\Http\Requests\StoreAttendanceEntryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAttendanceEntryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AttendanceEntry  $attendanceEntry
     * @return \Illuminate\Http\Response
     */
    public function show(AttendanceEntry $attendanceEntry)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AttendanceEntry  $attendanceEntry
     * @return \Illuminate\Http\Response
     */
    public function edit(AttendanceEntry $attendanceEntry)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAttendanceEntryRequest  $request
     * @param  \App\Models\AttendanceEntry  $attendanceEntry
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAttendanceEntryRequest $request, AttendanceEntry $attendanceEntry)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AttendanceEntry  $attendanceEntry
     * @return \Illuminate\Http\Response
     */
    public function destroy(AttendanceEntry $attendanceEntry)
    {
        //
    }
}
