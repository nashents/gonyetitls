<?php

namespace App\Http\Controllers;

use App\Models\EmptyRunExpense;
use App\Http\Requests\StoreEmptyRunExpenseRequest;
use App\Http\Requests\UpdateEmptyRunExpenseRequest;

class EmptyRunExpenseController extends Controller
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
     * @param  \App\Http\Requests\StoreEmptyRunExpenseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEmptyRunExpenseRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EmptyRunExpense  $emptyRunExpense
     * @return \Illuminate\Http\Response
     */
    public function show(EmptyRunExpense $emptyRunExpense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EmptyRunExpense  $emptyRunExpense
     * @return \Illuminate\Http\Response
     */
    public function edit(EmptyRunExpense $emptyRunExpense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateEmptyRunExpenseRequest  $request
     * @param  \App\Models\EmptyRunExpense  $emptyRunExpense
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEmptyRunExpenseRequest $request, EmptyRunExpense $emptyRunExpense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmptyRunExpense  $emptyRunExpense
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmptyRunExpense $emptyRunExpense)
    {
        //
    }
}
