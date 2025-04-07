<?php

namespace App\Http\Controllers;

use App\Models\PayslipItem;
use App\Http\Requests\StorePayslipItemRequest;
use App\Http\Requests\UpdatePayslipItemRequest;

class PayslipItemController extends Controller
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
     * @param  \App\Http\Requests\StorePayslipItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePayslipItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PayslipItem  $payslipItem
     * @return \Illuminate\Http\Response
     */
    public function show(PayslipItem $payslipItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PayslipItem  $payslipItem
     * @return \Illuminate\Http\Response
     */
    public function edit(PayslipItem $payslipItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePayslipItemRequest  $request
     * @param  \App\Models\PayslipItem  $payslipItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePayslipItemRequest $request, PayslipItem $payslipItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PayslipItem  $payslipItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(PayslipItem $payslipItem)
    {
        //
    }
}
