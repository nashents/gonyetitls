<?php

namespace App\Http\Controllers;

use App\Models\PayrollSalaryDetail;
use App\Http\Requests\StorePayrollSalaryDetailRequest;
use App\Http\Requests\UpdatePayrollSalaryDetailRequest;

class PayrollSalaryDetailController extends Controller
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
     * @param  \App\Http\Requests\StorePayrollSalaryDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePayrollSalaryDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PayrollSalaryDetail  $payrollSalaryDetail
     * @return \Illuminate\Http\Response
     */
    public function show(PayrollSalaryDetail $payrollSalaryDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PayrollSalaryDetail  $payrollSalaryDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(PayrollSalaryDetail $payrollSalaryDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePayrollSalaryDetailRequest  $request
     * @param  \App\Models\PayrollSalaryDetail  $payrollSalaryDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePayrollSalaryDetailRequest $request, PayrollSalaryDetail $payrollSalaryDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PayrollSalaryDetail  $payrollSalaryDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(PayrollSalaryDetail $payrollSalaryDetail)
    {
        //
    }
}
