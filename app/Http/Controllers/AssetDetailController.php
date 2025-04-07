<?php

namespace App\Http\Controllers;

use App\Models\AssetDetail;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreAssetDetailRequest;
use App\Http\Requests\UpdateAssetDetailRequest;

class AssetDetailController extends Controller
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
     * @param  \App\Http\Requests\StoreAssetDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAssetDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AssetDetail  $assetDetail
     * @return \Illuminate\Http\Response
     */
    public function show(AssetDetail $assetDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AssetDetail  $assetDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(AssetDetail $assetDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAssetDetailRequest  $request
     * @param  \App\Models\AssetDetail  $assetDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAssetDetailRequest $request, AssetDetail $assetDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AssetDetail  $assetDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(AssetDetail $assetDetail)
    {
        $assetDetail->delete();
        Session::flash('success','Serial deleted successfully');
        return redirect()->back();
    }
}
