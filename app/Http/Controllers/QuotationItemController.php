<?php

namespace App\Http\Controllers;

use App\Models\QuotationItem;
use App\Http\Requests\StoreQuotationItemRequest;
use App\Http\Requests\UpdateQuotationItemRequest;

class QuotationItemController extends Controller
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
     * @param  \App\Http\Requests\StoreQuotationItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreQuotationItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\QuotationItem  $quotationItem
     * @return \Illuminate\Http\Response
     */
    public function show(QuotationItem $quotationItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\QuotationItem  $quotationItem
     * @return \Illuminate\Http\Response
     */
    public function edit(QuotationItem $quotationItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateQuotationItemRequest  $request
     * @param  \App\Models\QuotationItem  $quotationItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateQuotationItemRequest $request, QuotationItem $quotationItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\QuotationItem  $quotationItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(QuotationItem $quotationItem)
    {
        //
    }
}
