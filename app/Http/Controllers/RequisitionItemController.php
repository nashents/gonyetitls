<?php

namespace App\Http\Controllers;

use App\Models\RequisitionItem;
use App\Http\Requests\StoreRequisitionItemRequest;
use App\Http\Requests\UpdateRequisitionItemRequest;

class RequisitionItemController extends Controller
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
     * @param  \App\Http\Requests\StoreRequisitionItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequisitionItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RequisitionItem  $requisitionItem
     * @return \Illuminate\Http\Response
     */
    public function show(RequisitionItem $requisitionItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RequisitionItem  $requisitionItem
     * @return \Illuminate\Http\Response
     */
    public function edit(RequisitionItem $requisitionItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRequisitionItemRequest  $request
     * @param  \App\Models\RequisitionItem  $requisitionItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequisitionItemRequest $request, RequisitionItem $requisitionItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RequisitionItem  $requisitionItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(RequisitionItem $requisitionItem)
    {
        //
    }
}
