<?php

namespace App\Http\Controllers;

use App\Models\TrailerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TrailerGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('trailer_groups.index');
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
     * @param  \App\Models\TrailerGroup  $trailerGroup
     * @return \Illuminate\Http\Response
     */
    public function show(TrailerGroup $trailerGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TrailerGroup  $trailerGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(TrailerGroup $trailerGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TrailerGroup  $trailerGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TrailerGroup $trailerGroup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TrailerGroup  $trailerGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrailerGroup $trailerGroup)
    {
        $trailerGroup->delete();
        Session::flash('success','Trailer Group Deleted Successfully!!');
        return redirect()->back();
    }
}
