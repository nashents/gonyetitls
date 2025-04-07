<?php

namespace App\Http\Controllers;

use App\Models\TripType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TripTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('trip_types.index');
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
     * @param  \App\Models\TripType  $tripType
     * @return \Illuminate\Http\Response
     */
    public function show(TripType $tripType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TripType  $tripType
     * @return \Illuminate\Http\Response
     */
    public function edit(TripType $tripType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripType  $tripType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TripType $tripType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripType  $tripType
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripType $tripType)
    {
        $tripType->delete();
        Session::flash('success','Trip Type successfully deleted');
        return redirect()->back();
    }
}
