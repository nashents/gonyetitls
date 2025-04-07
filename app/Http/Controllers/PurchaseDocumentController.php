<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Models\PurchaseDocument;
use Illuminate\Support\Facades\Session;

class PurchaseDocumentController extends Controller
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
     * @param  \App\Models\PurchaseDocument  $purchaseDocument
     * @return \Illuminate\Http\Response
     */
    public function show(PurchaseDocument $purchaseDocument)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PurchaseDocument  $purchaseDocument
     * @return \Illuminate\Http\Response
     */
    public function edit(PurchaseDocument $purchaseDocument)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PurchaseDocument  $purchaseDocument
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseDocument $purchaseDocument)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PurchaseDocument  $purchaseDocument
     * @return \Illuminate\Http\Response
     */
    public function destroy(PurchaseDocument $purchaseDocument)
    {
        $purchase = Purchase::find($purchaseDocument->purchase_id);
        if ($purchase->authorization == "pending") {
            $purchaseDocument->delete();
            Session::flash('success','Quotation Deleted Successfully!!');
            return redirect()->back();
        }else {
            Session::flash('error','Cannot delete quotation. Purchase Order already authorized!!');
            return redirect()->back();
        }

    }
}
