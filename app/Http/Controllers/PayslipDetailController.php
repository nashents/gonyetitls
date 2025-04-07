<?php

namespace App\Http\Controllers;

use App\Models\PayslipDetail;
use App\Http\Requests\StorePayslipDetailRequest;
use App\Http\Requests\UpdatePayslipDetailRequest;

class PayslipDetailController extends Controller
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
     * @param  \App\Http\Requests\StorePayslipDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePayslipDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PayslipDetail  $payslipDetail
     * @return \Illuminate\Http\Response
     */
    public function show(PayslipDetail $payslipDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PayslipDetail  $payslipDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(PayslipDetail $payslipDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePayslipDetailRequest  $request
     * @param  \App\Models\PayslipDetail  $payslipDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePayslipDetailRequest $request, PayslipDetail $payslipDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PayslipDetail  $payslipDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(PayslipDetail $payslipDetail)
    {
        //
    }
}
