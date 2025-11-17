<?php

namespace App\Http\Controllers;

use App\Models\CostItem;
use App\Http\Requests\StoreCostItemRequest;
use App\Http\Requests\UpdateCostItemRequest;

class CostItemController extends Controller
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
     * @param  \App\Http\Requests\StoreCostItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCostItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CostItem  $costItem
     * @return \Illuminate\Http\Response
     */
    public function show(CostItem $costItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CostItem  $costItem
     * @return \Illuminate\Http\Response
     */
    public function edit(CostItem $costItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCostItemRequest  $request
     * @param  \App\Models\CostItem  $costItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCostItemRequest $request, CostItem $costItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CostItem  $costItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(CostItem $costItem)
    {
        //
    }
}
