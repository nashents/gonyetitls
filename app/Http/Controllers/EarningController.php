<?php

namespace App\Http\Controllers;

use App\Models\Earning;
use App\Http\Requests\StoreEarningRequest;
use App\Http\Requests\UpdateEarningRequest;

class EarningController extends Controller
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
     * @param  \App\Http\Requests\StoreEarningRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEarningRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Earning  $earning
     * @return \Illuminate\Http\Response
     */
    public function show(Earning $earning)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Earning  $earning
     * @return \Illuminate\Http\Response
     */
    public function edit(Earning $earning)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateEarningRequest  $request
     * @param  \App\Models\Earning  $earning
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEarningRequest $request, Earning $earning)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Earning  $earning
     * @return \Illuminate\Http\Response
     */
    public function destroy(Earning $earning)
    {
        //
    }
}
