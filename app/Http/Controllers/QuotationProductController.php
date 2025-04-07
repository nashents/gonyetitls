<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuotationProduct;
use Illuminate\Support\Facades\Session;

class QuotationProductController extends Controller
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
     * @param  \App\Models\QuotationProduct  $quotationProduct
     * @return \Illuminate\Http\Response
     */
    public function show(QuotationProduct $quotationProduct)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\QuotationProduct  $quotationProduct
     * @return \Illuminate\Http\Response
     */
    public function edit(QuotationProduct $quotationProduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\QuotationProduct  $quotationProduct
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, QuotationProduct $quotationProduct)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\QuotationProduct  $quotationProduct
     * @return \Illuminate\Http\Response
     */
    public function destroy(QuotationProduct $quotationProduct)
    {
        $quotationProduct->delete();
        Session::flash('success','Quotation Item Deleted Successfull!!');
        return redirect()->back();
    }
}
