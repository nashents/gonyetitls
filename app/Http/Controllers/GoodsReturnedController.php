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
        return view('goods_returneds.index');
    }

    public function assets()
    {
        return view('goods_returneds.assets');
    }
    public function tyres()
    {
        return view('goods_returneds.tyres');
    }
    public function pending()
    {
        return view('goods_returneds.pending');
    }
    public function approved()
    {
        return view('goods_returneds.approved');
    }
    public function rejected()
    {
        return view('goods_returneds.rejected');
    }

    /**
     * Item-picker / draft screen: choose an approved GRV, select which
     * lines to return with a reason and qty each. $goodsReturned (optional)
     * reopens an existing, still-editable draft. Named 'goods_returneds.new'
     * (not '.create') to avoid colliding with the resource route below.
     */
    public function newReturn($department, $goodsReturned = null)
    {
        return view('goods_returneds.create', [
            'department' => $department,
            'goodsReturnedId' => $goodsReturned,
        ]);
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
        $goodsReturned->load([
            'vendor', 'employee', 'user', 'authorized_by', 'goods_received', 'debit_note',
            'goods_returned_items.product',
        ]);

        return view('goods_returneds.show', compact('goodsReturned'));
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
