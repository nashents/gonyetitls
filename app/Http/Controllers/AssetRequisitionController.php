<?php

namespace App\Http\Controllers;

use App\Models\AssetRequisition;
use App\Http\Requests\StoreAssetRequisitionRequest;
use App\Http\Requests\UpdateAssetRequisitionRequest;

class AssetRequisitionController extends Controller
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
     * @param  \App\Http\Requests\StoreAssetRequisitionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAssetRequisitionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AssetRequisition  $assetRequisition
     * @return \Illuminate\Http\Response
     */
    public function show(AssetRequisition $assetRequisition)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AssetRequisition  $assetRequisition
     * @return \Illuminate\Http\Response
     */
    public function edit(AssetRequisition $assetRequisition)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAssetRequisitionRequest  $request
     * @param  \App\Models\AssetRequisition  $assetRequisition
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAssetRequisitionRequest $request, AssetRequisition $assetRequisition)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AssetRequisition  $assetRequisition
     * @return \Illuminate\Http\Response
     */
    public function destroy(AssetRequisition $assetRequisition)
    {
        //
    }
}
