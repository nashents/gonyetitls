<?php

namespace App\Http\Controllers;

use App\Models\Mileage;
use App\Http\Requests\StoreMileageRequest;
use App\Http\Requests\UpdateMileageRequest;

class MileageController extends Controller
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
     * @param  \App\Http\Requests\StoreMileageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMileageRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mileage  $mileage
     * @return \Illuminate\Http\Response
     */
    public function show(Mileage $mileage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mileage  $mileage
     * @return \Illuminate\Http\Response
     */
    public function edit(Mileage $mileage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMileageRequest  $request
     * @param  \App\Models\Mileage  $mileage
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMileageRequest $request, Mileage $mileage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mileage  $mileage
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mileage $mileage)
    {
        //
    }
}
