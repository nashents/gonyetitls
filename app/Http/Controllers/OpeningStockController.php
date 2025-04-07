<?php

namespace App\Http\Controllers;

use App\Models\OpeningStock;
use App\Http\Requests\StoreOpeningStockRequest;
use App\Http\Requests\UpdateOpeningStockRequest;

class OpeningStockController extends Controller
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
     * @param  \App\Http\Requests\StoreOpeningStockRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOpeningStockRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OpeningStock  $openingStock
     * @return \Illuminate\Http\Response
     */
    public function show(OpeningStock $openingStock)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OpeningStock  $openingStock
     * @return \Illuminate\Http\Response
     */
    public function edit(OpeningStock $openingStock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateOpeningStockRequest  $request
     * @param  \App\Models\OpeningStock  $openingStock
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOpeningStockRequest $request, OpeningStock $openingStock)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OpeningStock  $openingStock
     * @return \Illuminate\Http\Response
     */
    public function destroy(OpeningStock $openingStock)
    {
        //
    }
}
