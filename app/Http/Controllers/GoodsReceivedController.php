<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceived;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreGoodsReceivedRequest;
use App\Http\Requests\UpdateGoodsReceivedRequest;

class GoodsReceivedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('goods_receiveds.index');
    }
    public function assets()
    {
        return view('goods_receiveds.assets');
    }
    public function tyres()
    {
        return view('goods_receiveds.tyres');
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
     * @param  \App\Http\Requests\StoreGoodsReceivedRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreGoodsReceivedRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GoodsReceived  $goodsReceived
     * @return \Illuminate\Http\Response
     */
    public function show(GoodsReceived $goodsReceived)
    {
        return view('goods_receiveds.show')->with('goods_received',$goodsReceived);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GoodsReceived  $goodsReceived
     * @return \Illuminate\Http\Response
     */
    public function edit(GoodsReceived $goodsReceived)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateGoodsReceivedRequest  $request
     * @param  \App\Models\GoodsReceived  $goodsReceived
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGoodsReceivedRequest $request, GoodsReceived $goodsReceived)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GoodsReceived  $goodsReceived
     * @return \Illuminate\Http\Response
     */
    public function destroy(GoodsReceived $goodsReceived)
    {
        $inventories = $goodsReceived->inventories;   
        $assets = $goodsReceived->assets;   
        $tyres = $goodsReceived->tyres;   
        if (isset($inventories)) {
            foreach ($inventories as $inventory) {
                $inventory->delete();
            }
        }
        if (isset($tyres)) {
            foreach ($tyres as $tyre) {
                $tyre->delete();
            }
        }
        if (isset($assets)) {
            foreach ($assets as $asset) {
                $asset->delete();
            }
        }
        $goodsReceived->delete();
        Session::flash('success','Goods Received Voucher Deleted Successfully');
        return redirect()->back();
    }
}
