<?php

namespace App\Http\Controllers;

use App\Models\PublicHoliday;
use App\Http\Requests\StorePublicHolidayRequest;
use App\Http\Requests\UpdatePublicHolidayRequest;

class PublicHolidayController extends Controller
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
     * @param  \App\Http\Requests\StorePublicHolidayRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePublicHolidayRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PublicHoliday  $publicHoliday
     * @return \Illuminate\Http\Response
     */
    public function show(PublicHoliday $publicHoliday)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PublicHoliday  $publicHoliday
     * @return \Illuminate\Http\Response
     */
    public function edit(PublicHoliday $publicHoliday)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePublicHolidayRequest  $request
     * @param  \App\Models\PublicHoliday  $publicHoliday
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePublicHolidayRequest $request, PublicHoliday $publicHoliday)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PublicHoliday  $publicHoliday
     * @return \Illuminate\Http\Response
     */
    public function destroy(PublicHoliday $publicHoliday)
    {
        //
    }
}
