<?php

namespace App\Http\Controllers;

use App\Models\InvoiceCount;
use App\Http\Requests\StoreInvoiceCountRequest;
use App\Http\Requests\UpdateInvoiceCountRequest;

class InvoiceCountController extends Controller
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
     * @param  \App\Http\Requests\StoreInvoiceCountRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInvoiceCountRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InvoiceCount  $invoiceCount
     * @return \Illuminate\Http\Response
     */
    public function show(InvoiceCount $invoiceCount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InvoiceCount  $invoiceCount
     * @return \Illuminate\Http\Response
     */
    public function edit(InvoiceCount $invoiceCount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInvoiceCountRequest  $request
     * @param  \App\Models\InvoiceCount  $invoiceCount
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInvoiceCountRequest $request, InvoiceCount $invoiceCount)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InvoiceCount  $invoiceCount
     * @return \Illuminate\Http\Response
     */
    public function destroy(InvoiceCount $invoiceCount)
    {
        //
    }
}
