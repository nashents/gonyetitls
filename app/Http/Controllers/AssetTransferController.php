<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssetTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
      public function index()
    {
       
        return view('asset_transfers.index')->with(
            [
                'department' => 'asset'
            ]
        );
    }
        public function pending()
    {
        return view('asset_transfers.pending')->with(
            [
                'department' => 'asset'
            ]
        );
    }
    public function approved()
    {
        return view('asset_transfers.approved')->with(
            [
                'department' => 'asset'
            ]
        );
    }
    public function rejected()
    {
        return view('asset_transfers.rejected')->with(
            [
                'department' => 'asset'
            ]
        );
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
