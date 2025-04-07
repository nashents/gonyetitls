<?php

namespace App\Http\Controllers;

use App\Models\SalePayment;
use App\Http\Requests\StoreSalePaymentRequest;
use App\Http\Requests\UpdateSalePaymentRequest;

class SalePaymentController extends Controller
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
     * @param  \App\Http\Requests\StoreSalePaymentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSalePaymentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SalePayment  $salePayment
     * @return \Illuminate\Http\Response
     */
    public function show(SalePayment $salePayment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalePayment  $salePayment
     * @return \Illuminate\Http\Response
     */
    public function edit(SalePayment $salePayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSalePaymentRequest  $request
     * @param  \App\Models\SalePayment  $salePayment
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSalePaymentRequest $request, SalePayment $salePayment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalePayment  $salePayment
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalePayment $salePayment)
    {
        //
    }
}
