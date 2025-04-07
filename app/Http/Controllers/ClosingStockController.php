<?php

namespace App\Http\Controllers;

use App\Models\ClosingStock;
use App\Http\Requests\StoreClosingStockRequest;
use App\Http\Requests\UpdateClosingStockRequest;

class ClosingStockController extends Controller
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
     * @param  \App\Http\Requests\StoreClosingStockRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreClosingStockRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ClosingStock  $closingStock
     * @return \Illuminate\Http\Response
     */
    public function show(ClosingStock $closingStock)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ClosingStock  $closingStock
     * @return \Illuminate\Http\Response
     */
    public function edit(ClosingStock $closingStock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateClosingStockRequest  $request
     * @param  \App\Models\ClosingStock  $closingStock
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClosingStockRequest $request, ClosingStock $closingStock)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ClosingStock  $closingStock
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClosingStock $closingStock)
    {
        //
    }
}
