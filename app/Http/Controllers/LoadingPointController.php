<?php

namespace App\Http\Controllers;

use App\Models\LoadingPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LoadingPointController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('loading_points.index');
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
     * @param  \App\Models\LoadingPoint  $loadingPoint
     * @return \Illuminate\Http\Response
     */
    public function show(LoadingPoint $loadingPoint)
    {
        return view('loading_points.show')->with('loading_point',$loadingPoint);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LoadingPoint  $loadingPoint
     * @return \Illuminate\Http\Response
     */
    public function edit(LoadingPoint $loadingPoint)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LoadingPoint  $loadingPoint
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LoadingPoint $loadingPoint)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LoadingPoint  $loadingPoint
     * @return \Illuminate\Http\Response
     */
    public function destroy(LoadingPoint $loadingPoint)
    {
        $loadingPoint->delete();
        Session::flash('success','Loading Point Deleted Successfully!!');
        return redirect()->back();

    }
}
