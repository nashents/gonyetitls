<?php

namespace App\Http\Controllers;

use App\Models\BillExpense;
use App\Http\Requests\StoreBillExpenseRequest;
use App\Http\Requests\UpdateBillExpenseRequest;

class BillExpenseController extends Controller
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
     * @param  \App\Http\Requests\StoreBillExpenseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBillExpenseRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BillExpense  $billExpense
     * @return \Illuminate\Http\Response
     */
    public function show(BillExpense $billExpense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BillExpense  $billExpense
     * @return \Illuminate\Http\Response
     */
    public function edit(BillExpense $billExpense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBillExpenseRequest  $request
     * @param  \App\Models\BillExpense  $billExpense
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBillExpenseRequest $request, BillExpense $billExpense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BillExpense  $billExpense
     * @return \Illuminate\Http\Response
     */
    public function destroy(BillExpense $billExpense)
    {
        
    }
}
