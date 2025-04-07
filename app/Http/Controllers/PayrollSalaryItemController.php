<?php

namespace App\Http\Controllers;

use App\Models\PayrollSalaryItem;
use App\Http\Requests\StorePayrollSalaryItemRequest;
use App\Http\Requests\UpdatePayrollSalaryItemRequest;

class PayrollSalaryItemController extends Controller
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
     * @param  \App\Http\Requests\StorePayrollSalaryItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePayrollSalaryItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PayrollSalaryItem  $payrollSalaryItem
     * @return \Illuminate\Http\Response
     */
    public function show(PayrollSalaryItem $payrollSalaryItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PayrollSalaryItem  $payrollSalaryItem
     * @return \Illuminate\Http\Response
     */
    public function edit(PayrollSalaryItem $payrollSalaryItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePayrollSalaryItemRequest  $request
     * @param  \App\Models\PayrollSalaryItem  $payrollSalaryItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePayrollSalaryItemRequest $request, PayrollSalaryItem $payrollSalaryItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PayrollSalaryItem  $payrollSalaryItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(PayrollSalaryItem $payrollSalaryItem)
    {
        //
    }
}
