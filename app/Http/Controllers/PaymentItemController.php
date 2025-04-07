<?php

namespace App\Http\Controllers;

use App\Models\PaymentItem;
use App\Http\Requests\StorePaymentItemRequest;
use App\Http\Requests\UpdatePaymentItemRequest;

class PaymentItemController extends Controller
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
     * @param  \App\Http\Requests\StorePaymentItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePaymentItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PaymentItem  $paymentItem
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentItem $paymentItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PaymentItem  $paymentItem
     * @return \Illuminate\Http\Response
     */
    public function edit(PaymentItem $paymentItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePaymentItemRequest  $request
     * @param  \App\Models\PaymentItem  $paymentItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePaymentItemRequest $request, PaymentItem $paymentItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaymentItem  $paymentItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentItem $paymentItem)
    {
        //
    }
}
