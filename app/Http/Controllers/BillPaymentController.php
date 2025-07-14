<?php

namespace App\Http\Controllers;

use App\Models\BillPayment;
use App\Http\Requests\StoreBillPaymentRequest;
use App\Http\Requests\UpdateBillPaymentRequest;

class BillPaymentController extends Controller
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
     * @param  \App\Http\Requests\StoreBillPaymentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBillPaymentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BillPayment  $billPayment
     * @return \Illuminate\Http\Response
     */
    public function show(BillPayment $billPayment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BillPayment  $billPayment
     * @return \Illuminate\Http\Response
     */
    public function edit(BillPayment $billPayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBillPaymentRequest  $request
     * @param  \App\Models\BillPayment  $billPayment
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBillPaymentRequest $request, BillPayment $billPayment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BillPayment  $billPayment
     * @return \Illuminate\Http\Response
     */
    public function destroy(BillPayment $billPayment)
    {
        //
    }
}
