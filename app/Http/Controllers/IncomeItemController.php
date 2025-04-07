<?php

namespace App\Http\Controllers;

use App\Models\IncomeItem;
use App\Http\Requests\StoreIncomeItemRequest;
use App\Http\Requests\UpdateIncomeItemRequest;

class IncomeItemController extends Controller
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
     * @param  \App\Http\Requests\StoreIncomeItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreIncomeItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\IncomeItem  $incomeItem
     * @return \Illuminate\Http\Response
     */
    public function show(IncomeItem $incomeItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\IncomeItem  $incomeItem
     * @return \Illuminate\Http\Response
     */
    public function edit(IncomeItem $incomeItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateIncomeItemRequest  $request
     * @param  \App\Models\IncomeItem  $incomeItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateIncomeItemRequest $request, IncomeItem $incomeItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\IncomeItem  $incomeItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(IncomeItem $incomeItem)
    {
        //
    }
}
