<?php

namespace App\Http\Controllers;

use App\Models\InventoryPurchase;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreInventoryPurchaseRequest;
use App\Http\Requests\UpdateInventoryPurchaseRequest;

class InventoryPurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('inventory_purchases.index');
    }
    public function manage()
    {
        return view('inventory_purchases.manage');
    }

    public function rejected()
    {
        return view('inventory_purchases.rejected');
    }
    public function pending()
    {
        return view('inventory_purchases.pending');
    }
    public function approved()
    {
        return view('inventory_purchases.approved');
    }
    public function deleted()
    {
        return view('inventory_purchases.deleted');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('inventory_purchases.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreInventoryPurchaseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInventoryPurchaseRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InventoryPurchase  $inventoryPurchase
     * @return \Illuminate\Http\Response
     */
    public function show(InventoryPurchase $inventoryPurchase)
    {
        return view('inventory_purchases.show')->with('inventory_purchase', $inventoryPurchase);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InventoryPurchase  $inventoryPurchase
     * @return \Illuminate\Http\Response
     */
    public function edit(InventoryPurchase $inventoryPurchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInventoryPurchaseRequest  $request
     * @param  \App\Models\InventoryPurchase  $inventoryPurchase
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInventoryPurchaseRequest $request, InventoryPurchase $inventoryPurchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InventoryPurchase  $inventoryPurchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(InventoryPurchase $inventoryPurchase)
    {
        $inventoryPurchase->delete();
        Session::flash('success','Purchase Order Deleted Successfully!!');
        return redirect()->back();
    }
}
