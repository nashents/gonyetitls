<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TyreProduct;
use App\Http\Requests\StoreTyreProductRequest;
use App\Http\Requests\UpdateTyreProductRequest;

class TyreProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('tyre_products.index');
    }
    public function manage()
    {
        return view('tyre_products.manage');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tyre_products.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTyreProductRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTyreProductRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TyreProduct  $tyreProduct
     * @return \Illuminate\Http\Response
     */
    public function show(TyreProduct $tyreProduct)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TyreProduct  $tyreProduct
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::find($id);
        return view('tyre_products.edit')->with([
        'product'=>$product
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTyreProductRequest  $request
     * @param  \App\Models\TyreProduct  $tyreProduct
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTyreProductRequest $request, TyreProduct $tyreProduct)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TyreProduct  $tyreProduct
     * @return \Illuminate\Http\Response
     */
    public function destroy(TyreProduct $tyreProduct)
    {
        //
    }
}
