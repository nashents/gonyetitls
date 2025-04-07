<?php

namespace App\Http\Controllers;

use App\Models\Dispose;
use App\Http\Requests\StoreDisposeRequest;
use App\Http\Requests\UpdateDisposeRequest;

class DisposeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('disposes.index');
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
     * @param  \App\Http\Requests\StoreDisposeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDisposeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Dispose  $dispose
     * @return \Illuminate\Http\Response
     */
    public function show(Dispose $dispose)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Dispose  $dispose
     * @return \Illuminate\Http\Response
     */
    public function edit(Dispose $dispose)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDisposeRequest  $request
     * @param  \App\Models\Dispose  $dispose
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDisposeRequest $request, Dispose $dispose)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Dispose  $dispose
     * @return \Illuminate\Http\Response
     */
    public function destroy(Dispose $dispose)
    {
        //
    }
}
