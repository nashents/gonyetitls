<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OffloadingPoint;
use Illuminate\Support\Facades\Session;

class OffloadingPointController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('offloading_points.index');
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
     * @param  \App\Models\OffloadingPoint  $offloadingPoint
     * @return \Illuminate\Http\Response
     */
    public function show(OffloadingPoint $offloadingPoint)
    {
        return view('offloading_points.show')->with('offloading_point',$offloadingPoint);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OffloadingPoint  $offloadingPoint
     * @return \Illuminate\Http\Response
     */
    public function edit(OffloadingPoint $offloadingPoint)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OffloadingPoint  $offloadingPoint
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OffloadingPoint $offloadingPoint)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OffloadingPoint  $offloadingPoint
     * @return \Illuminate\Http\Response
     */
    public function destroy(OffloadingPoint $offloadingPoint)
    {
        $offloadingPoint->delete();
        Session::flash('success','Offloading Point Deleted Successfully!!');
        return redirect()->back();
    }
}
