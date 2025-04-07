<?php

namespace App\Http\Controllers;

use App\Models\HorseGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class HorseGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('horse_groups.index');
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
     * @param  \App\Models\HorseGroup  $horseGroup
     * @return \Illuminate\Http\Response
     */
    public function show(HorseGroup $horseGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HorseGroup  $horseGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(HorseGroup $horseGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HorseGroup  $horseGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HorseGroup $horseGroup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HorseGroup  $horseGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(HorseGroup $horseGroup)
    {
        $horseGroup->delete();
        Session::flash('success','Horse Group Deleted Successfully!!');
        return redirect()->back();
    }
}
