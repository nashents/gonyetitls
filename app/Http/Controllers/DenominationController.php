<?php

namespace App\Http\Controllers;

use App\Models\Denomination;
use App\Http\Requests\StoreDenominationRequest;
use App\Http\Requests\UpdateDenominationRequest;

class DenominationController extends Controller
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
     * @param  \App\Http\Requests\StoreDenominationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDenominationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Denomination  $denomination
     * @return \Illuminate\Http\Response
     */
    public function show(Denomination $denomination)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Denomination  $denomination
     * @return \Illuminate\Http\Response
     */
    public function edit(Denomination $denomination)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDenominationRequest  $request
     * @param  \App\Models\Denomination  $denomination
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDenominationRequest $request, Denomination $denomination)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Denomination  $denomination
     * @return \Illuminate\Http\Response
     */
    public function destroy(Denomination $denomination)
    {
        //
    }
}
