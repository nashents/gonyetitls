<?php

namespace App\Http\Controllers;

use App\Models\RateCard;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreRateCardRequest;
use App\Http\Requests\UpdateRateCardRequest;

class RateCardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('rate_cards.index');
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
     * @param  \App\Http\Requests\StoreRateCardRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRateCardRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RateCard  $rateCard
     * @return \Illuminate\Http\Response
     */
    public function show(RateCard $rateCard)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RateCard  $rateCard
     * @return \Illuminate\Http\Response
     */
    public function edit(RateCard $rateCard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRateCardRequest  $request
     * @param  \App\Models\RateCard  $rateCard
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRateCardRequest $request, RateCard $rateCard)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RateCard  $rateCard
     * @return \Illuminate\Http\Response
     */
    public function destroy(RateCard $rateCard)
    {
        $rateCard->delete();
        Session::flash('success','Rate Deleted Successfully!!');
        return redirect()->back();
    }
}
