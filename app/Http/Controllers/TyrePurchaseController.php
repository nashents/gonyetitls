<?php

namespace App\Http\Controllers;

use App\Models\TyrePurchase;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreTyrePurchaseRequest;
use App\Http\Requests\UpdateTyrePurchaseRequest;

class TyrePurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('tyre_purchases.index');
    }
    public function manage()
    {
        return view('tyre_purchases.manage');
    }

    public function rejected()
    {
        return view('tyre_purchases.rejected');
    }
    public function pending()
    {
        return view('tyre_purchases.pending');
    }
    public function approved()
    {
        return view('tyre_purchases.approved');
    }
    public function deleted()
    {
        return view('tyre_purchases.deleted');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tyre_purchases.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTyrePurchaseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTyrePurchaseRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TyrePurchase  $tyrePurchase
     * @return \Illuminate\Http\Response
     */
    public function show(TyrePurchase $tyrePurchase)
    {
        return view('tyre_purchases.show')->with('tyre_purchase', $tyrePurchase);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TyrePurchase  $tyrePurchase
     * @return \Illuminate\Http\Response
     */
    public function edit(TyrePurchase $tyrePurchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTyrePurchaseRequest  $request
     * @param  \App\Models\TyrePurchase  $tyrePurchase
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTyrePurchaseRequest $request, TyrePurchase $tyrePurchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TyrePurchase  $tyrePurchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(TyrePurchase $tyrePurchase)
    {
        $tyrePurchase->delete();
        Session::flash('success','Purchase Order Deleted Successfully!!');
        return redirect()->back();
    }
}
