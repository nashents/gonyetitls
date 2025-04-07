<?php

namespace App\Http\Controllers;

use App\Models\ProductService;
use App\Http\Requests\StoreProductServiceRequest;
use App\Http\Requests\UpdateProductServiceRequest;

class ProductServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('product_services.index');
    }
    public function all($category)
    {
        return view('product_services.index')->with('category',$category);
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
     * @param  \App\Http\Requests\StoreProductServiceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductServiceRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductService  $productService
     * @return \Illuminate\Http\Response
     */
    public function show(ProductService $productService)
    {
        return view('product_services.show')->with('product',$productService);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductService  $productService
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductService $productService)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProductServiceRequest  $request
     * @param  \App\Models\ProductService  $productService
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProductServiceRequest $request, ProductService $productService)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductService  $productService
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductService $productService)
    {
        //
    }
}
