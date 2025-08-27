<?php

namespace App\Http\Controllers;

use App\Models\GoodsReturned;
use App\Http\Requests\StoreGoodsReturnedRequest;
use App\Http\Requests\UpdateGoodsReturnedRequest;

class GoodsReturnedController extends Controller
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
     * @param  \App\Http\Requests\StoreGoodsReturnedRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreGoodsReturnedRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GoodsReturned  $goodsReturned
     * @return \Illuminate\Http\Response
     */
    public function show(GoodsReturned $goodsReturned)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GoodsReturned  $goodsReturned
     * @return \Illuminate\Http\Response
     */
    public function edit(GoodsReturned $goodsReturned)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateGoodsReturnedRequest  $request
     * @param  \App\Models\GoodsReturned  $goodsReturned
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGoodsReturnedRequest $request, GoodsReturned $goodsReturned)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GoodsReturned  $goodsReturned
     * @return \Illuminate\Http\Response
     */
    public function destroy(GoodsReturned $goodsReturned)
    {
        //
    }
}
