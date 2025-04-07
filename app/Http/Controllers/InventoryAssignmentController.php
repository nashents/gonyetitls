<?php

namespace App\Http\Controllers;

use App\Models\InventoryAssignment;
use Illuminate\Http\Request;

class InventoryAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('inventory_assignments.index');
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InventoryAssignment  $inventoryAssignment
     * @return \Illuminate\Http\Response
     */
    public function show(InventoryAssignment $inventoryAssignment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InventoryAssignment  $inventoryAssignment
     * @return \Illuminate\Http\Response
     */
    public function edit(InventoryAssignment $inventoryAssignment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InventoryAssignment  $inventoryAssignment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InventoryAssignment $inventoryAssignment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InventoryAssignment  $inventoryAssignment
     * @return \Illuminate\Http\Response
     */
    public function destroy(InventoryAssignment $inventoryAssignment)
    {
        //
    }
}
