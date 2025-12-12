<?php

namespace App\Http\Controllers;

use App\Models\TyreTransfer;
use App\Http\Requests\StoreTyreTransferRequest;
use App\Http\Requests\UpdateTyreTransferRequest;

class TyreTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
       public function index()
    {
        return view('transfers.index')->with(
            [
                'department' => 'tyre'
            ]
        );
    }
        public function pending()
    {
        return view('transfers.pending')->with(
            [
                'department' => 'tyre'
            ]
        );
    }
    public function approved()
    {
        return view('transfers.approved')->with(
            [
                'department' => 'tyre'
            ]
        );
    }
    public function rejected()
    {
        return view('transfers.rejected')->with(
            [
                'department' => 'tyre'
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
     * @param  \App\Http\Requests\StoreTyreTransferRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTyreTransferRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TyreTransfer  $tyreTransfer
     * @return \Illuminate\Http\Response
     */
    public function show(TyreTransfer $tyreTransfer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TyreTransfer  $tyreTransfer
     * @return \Illuminate\Http\Response
     */
    public function edit(TyreTransfer $tyreTransfer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTyreTransferRequest  $request
     * @param  \App\Models\TyreTransfer  $tyreTransfer
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTyreTransferRequest $request, TyreTransfer $tyreTransfer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TyreTransfer  $tyreTransfer
     * @return \Illuminate\Http\Response
     */
    public function destroy(TyreTransfer $tyreTransfer)
    {
        //
    }
}
