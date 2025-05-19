<?php

namespace App\Http\Controllers;

use App\Models\Rehandling;
use App\Http\Requests\StoreRehandlingRequest;
use App\Http\Requests\UpdateRehandlingRequest;

class RehandlingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         return view('rehandlings.index');
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
     * @param  \App\Http\Requests\StoreRehandlingRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRehandlingRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Rehandling  $rehandling
     * @return \Illuminate\Http\Response
     */
    public function show(Rehandling $rehandling)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Rehandling  $rehandling
     * @return \Illuminate\Http\Response
     */
    public function edit(Rehandling $rehandling)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRehandlingRequest  $request
     * @param  \App\Models\Rehandling  $rehandling
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRehandlingRequest $request, Rehandling $rehandling)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Rehandling  $rehandling
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rehandling $rehandling)
    {
        //
    }
}
