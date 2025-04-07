<?php

namespace App\Http\Controllers;

use App\Models\QuotationCount;
use App\Http\Requests\StoreQuotationCountRequest;
use App\Http\Requests\UpdateQuotationCountRequest;

class QuotationCountController extends Controller
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
     * @param  \App\Http\Requests\StoreQuotationCountRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreQuotationCountRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\QuotationCount  $quotationCount
     * @return \Illuminate\Http\Response
     */
    public function show(QuotationCount $quotationCount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\QuotationCount  $quotationCount
     * @return \Illuminate\Http\Response
     */
    public function edit(QuotationCount $quotationCount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateQuotationCountRequest  $request
     * @param  \App\Models\QuotationCount  $quotationCount
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateQuotationCountRequest $request, QuotationCount $quotationCount)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\QuotationCount  $quotationCount
     * @return \Illuminate\Http\Response
     */
    public function destroy(QuotationCount $quotationCount)
    {
        //
    }
}
