<?php

namespace App\Http\Controllers;

use App\Models\ImmediateCause;
use App\Http\Requests\StoreImmediateCauseRequest;
use App\Http\Requests\UpdateImmediateCauseRequest;

class ImmediateCauseController extends Controller
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
     * @param  \App\Http\Requests\StoreImmediateCauseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreImmediateCauseRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImmediateCause  $immediateCause
     * @return \Illuminate\Http\Response
     */
    public function show(ImmediateCause $immediateCause)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ImmediateCause  $immediateCause
     * @return \Illuminate\Http\Response
     */
    public function edit(ImmediateCause $immediateCause)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateImmediateCauseRequest  $request
     * @param  \App\Models\ImmediateCause  $immediateCause
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateImmediateCauseRequest $request, ImmediateCause $immediateCause)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ImmediateCause  $immediateCause
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImmediateCause $immediateCause)
    {
        //
    }
}
