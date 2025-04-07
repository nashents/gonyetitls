<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTrip;
use App\Http\Requests\StoreInvoiceTripRequest;
use App\Http\Requests\UpdateInvoiceTripRequest;

class InvoiceTripController extends Controller
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
     * @param  \App\Http\Requests\StoreInvoiceTripRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInvoiceTripRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InvoiceTrip  $invoiceTrip
     * @return \Illuminate\Http\Response
     */
    public function show(InvoiceTrip $invoiceTrip)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InvoiceTrip  $invoiceTrip
     * @return \Illuminate\Http\Response
     */
    public function edit(InvoiceTrip $invoiceTrip)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInvoiceTripRequest  $request
     * @param  \App\Models\InvoiceTrip  $invoiceTrip
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInvoiceTripRequest $request, InvoiceTrip $invoiceTrip)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InvoiceTrip  $invoiceTrip
     * @return \Illuminate\Http\Response
     */
    public function destroy(InvoiceTrip $invoiceTrip)
    {
        //
    }
}
