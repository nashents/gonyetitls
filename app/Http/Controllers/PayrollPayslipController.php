<?php

namespace App\Http\Controllers;

use App\Models\PayrollPayslip;
use App\Http\Requests\StorePayrollPayslipRequest;
use App\Http\Requests\UpdatePayrollPayslipRequest;

class PayrollPayslipController extends Controller
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
     * @param  \App\Http\Requests\StorePayrollPayslipRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePayrollPayslipRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PayrollPayslip  $payrollPayslip
     * @return \Illuminate\Http\Response
     */
    public function show(PayrollPayslip $payrollPayslip)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PayrollPayslip  $payrollPayslip
     * @return \Illuminate\Http\Response
     */
    public function edit(PayrollPayslip $payrollPayslip)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePayrollPayslipRequest  $request
     * @param  \App\Models\PayrollPayslip  $payrollPayslip
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePayrollPayslipRequest $request, PayrollPayslip $payrollPayslip)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PayrollPayslip  $payrollPayslip
     * @return \Illuminate\Http\Response
     */
    public function destroy(PayrollPayslip $payrollPayslip)
    {
        //
    }
}
