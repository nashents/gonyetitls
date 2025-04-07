<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetDocument;
use Illuminate\Support\Facades\Session;

class AssetDocumentController extends Controller
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AssetDocument  $assetDocument
     * @return \Illuminate\Http\Response
     */
    public function show(AssetDocument $assetDocument)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AssetDocument  $assetDocument
     * @return \Illuminate\Http\Response
     */
    public function edit(AssetDocument $assetDocument)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AssetDocument  $assetDocument
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AssetDocument $assetDocument)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AssetDocument  $assetDocument
     * @return \Illuminate\Http\Response
     */
    public function destroy(AssetDocument $assetDocument)
    {
        $assetDocument->delete();
        Session::flash('success','Document deleted successfully');
        return redirect()->back();
    }
}
