<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InvoiceProduct;
use Illuminate\Support\Facades\Session;

class InvoiceProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('invoice_products.index');
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InvoiceProduct  $invoiceProduct
     * @return \Illuminate\Http\Response
     */
    public function show(InvoiceProduct $invoiceProduct)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InvoiceProduct  $invoiceProduct
     * @return \Illuminate\Http\Response
     */
    public function edit(InvoiceProduct $invoiceProduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InvoiceProduct  $invoiceProduct
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InvoiceProduct $invoiceProduct)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InvoiceProduct  $invoiceProduct
     * @return \Illuminate\Http\Response
     */
    public function destroy(InvoiceProduct $invoiceProduct)
    {
        $invoiceProduct->delete();
        Session::flash('success','Product / Service Deleted Successfully');
        return redirect()->back();
    }
}
