<?php

namespace App\Http\Controllers;

use App\Models\HorseType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class HorseTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('horse_types.index');
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
     * @param  \App\Models\HorseType  $horseType
     * @return \Illuminate\Http\Response
     */
    public function show(HorseType $horseType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HorseType  $horseType
     * @return \Illuminate\Http\Response
     */
    public function edit(HorseType $horseType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HorseType  $horseType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HorseType $horseType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HorseType  $horseType
     * @return \Illuminate\Http\Response
     */
    public function destroy(HorseType $horseType)
    {
        $horseType->delete();
        Session::flash('success','Horse Type Deleted Successfully!!');
        return redirect()->back();
    }
}
