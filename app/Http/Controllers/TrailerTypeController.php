<?php

namespace App\Http\Controllers;

use App\Models\TrailerType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TrailerTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('trailer_types.index');
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
     * @param  \App\Models\TrailerType  $trailerType
     * @return \Illuminate\Http\Response
     */
    public function show(TrailerType $trailerType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TrailerType  $trailerType
     * @return \Illuminate\Http\Response
     */
    public function edit(TrailerType $trailerType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TrailerType  $trailerType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TrailerType $trailerType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TrailerType  $trailerType
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrailerType $trailerType)
    {
        $trailerType->delete();
        Session::flash('success','Trailer Type Deleted Successfully!!');
        return redirect()->back();
    }
}
