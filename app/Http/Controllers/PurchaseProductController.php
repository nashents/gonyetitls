<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Models\PurchaseProduct;
use Illuminate\Support\Facades\Session;

class PurchaseProductController extends Controller
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
     * @param  \App\Models\PurchaseProduct  $purchaseProduct
     * @return \Illuminate\Http\Response
     */
    public function show(PurchaseProduct $purchaseProduct)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PurchaseProduct  $purchaseProduct
     * @return \Illuminate\Http\Response
     */
    public function edit(PurchaseProduct $purchaseProduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PurchaseProduct  $purchaseProduct
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseProduct $purchaseProduct)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PurchaseProduct  $purchaseProduct
     * @return \Illuminate\Http\Response
     */
    public function destroy(PurchaseProduct $purchaseProduct)
    {
        $purchase = Purchase::find($purchaseProduct->purchase_id);
        if ($purchase->authorization == "pending") {
            $purchaseProduct->delete();
            Session::flash('success','Product Deleted Successfully!!');
            return redirect()->back();
        }else {
            Session::flash('error','Cannot delete product. Purchase Order already authorized!!');
            return redirect()->back();
        }

    }
}
