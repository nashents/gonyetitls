<?php

namespace App\Http\Controllers;

use App\Models\Corridor;
use App\Http\Requests\StoreCorridorRequest;
use App\Http\Requests\UpdateCorridorRequest;

class CorridorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('corridors.index');
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
     * @param  \App\Http\Requests\StoreCorridorRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCorridorRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Corridor  $corridor
     * @return \Illuminate\Http\Response
     */
    public function show(Corridor $corridor)
    {
        return view('corridors.show')->with('corridor',$corridor);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Corridor  $corridor
     * @return \Illuminate\Http\Response
     */
    public function edit(Corridor $corridor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCorridorRequest  $request
     * @param  \App\Models\Corridor  $corridor
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCorridorRequest $request, Corridor $corridor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Corridor  $corridor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Corridor $corridor)
    {
        //
    }
}
