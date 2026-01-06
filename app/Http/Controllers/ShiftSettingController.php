<?php

namespace App\Http\Controllers;

use App\Models\ShiftSetting;
use App\Http\Requests\StoreShiftSettingRequest;
use App\Http\Requests\UpdateShiftSettingRequest;

class ShiftSettingController extends Controller
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
     * @param  \App\Http\Requests\StoreShiftSettingRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreShiftSettingRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ShiftSetting  $shiftSetting
     * @return \Illuminate\Http\Response
     */
    public function show(ShiftSetting $shiftSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ShiftSetting  $shiftSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(ShiftSetting $shiftSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateShiftSettingRequest  $request
     * @param  \App\Models\ShiftSetting  $shiftSetting
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateShiftSettingRequest $request, ShiftSetting $shiftSetting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ShiftSetting  $shiftSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(ShiftSetting $shiftSetting)
    {
        //
    }
}
