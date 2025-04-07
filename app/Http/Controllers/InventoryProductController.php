<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\InventoryProduct;

class InventoryProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('inventory_products.index');
    }
    public function manage()
    {
        return view('inventory_products.manage');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('inventory_products.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InventoryProduct  $inventoryProduct
     * @return \Illuminate\Http\Response
     */
    public function show(InventoryProduct $inventoryProduct)
    {
        return view('products.show')->with('product', $inventoryProduct);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InventoryProduct  $inventoryProduct
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::find($id);
        return view('inventory_products.edit')->with('product', $product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InventoryProduct  $inventoryProduct
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InventoryProduct $inventoryProduct)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InventoryProduct  $inventoryProduct
     * @return \Illuminate\Http\Response
     */
    public function destroy(InventoryProduct $inventoryProduct)
    {
        //
    }
}
