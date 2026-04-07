<?php

namespace App\Http\Controllers;

use App\Models\ComponentSlot;
use App\Http\Requests\StoreComponentSlotRequest;
use App\Http\Requests\UpdateComponentSlotRequest;

class ComponentSlotController extends Controller
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
     * @param  \App\Http\Requests\StoreComponentSlotRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreComponentSlotRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ComponentSlot  $componentSlot
     * @return \Illuminate\Http\Response
     */
    public function show(ComponentSlot $componentSlot)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ComponentSlot  $componentSlot
     * @return \Illuminate\Http\Response
     */
    public function edit(ComponentSlot $componentSlot)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateComponentSlotRequest  $request
     * @param  \App\Models\ComponentSlot  $componentSlot
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateComponentSlotRequest $request, ComponentSlot $componentSlot)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ComponentSlot  $componentSlot
     * @return \Illuminate\Http\Response
     */
    public function destroy(ComponentSlot $componentSlot)
    {
        //
    }
}
