<?php

namespace App\Http\Controllers;

use App\Models\GoodsReturnedItem;
use App\Http\Requests\StoreGoodsReturnedItemRequest;
use App\Http\Requests\UpdateGoodsReturnedItemRequest;

class GoodsReturnedItemController extends Controller
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
     * @param  \App\Http\Requests\StoreGoodsReturnedItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreGoodsReturnedItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GoodsReturnedItem  $goodsReturnedItem
     * @return \Illuminate\Http\Response
     */
    public function show(GoodsReturnedItem $goodsReturnedItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GoodsReturnedItem  $goodsReturnedItem
     * @return \Illuminate\Http\Response
     */
    public function edit(GoodsReturnedItem $goodsReturnedItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateGoodsReturnedItemRequest  $request
     * @param  \App\Models\GoodsReturnedItem  $goodsReturnedItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGoodsReturnedItemRequest $request, GoodsReturnedItem $goodsReturnedItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GoodsReturnedItem  $goodsReturnedItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(GoodsReturnedItem $goodsReturnedItem)
    {
        //
    }
}
