<?php

namespace App\Http\Controllers;

use App\Models\HorseMake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class HorseMakeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('horse_makes.index');
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
     * @param  \App\Models\HorseMake  $horseMake
     * @return \Illuminate\Http\Response
     */
    public function show(HorseMake $horseMake)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HorseMake  $horseMake
     * @return \Illuminate\Http\Response
     */
    public function edit(HorseMake $horseMake)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HorseMake  $horseMake
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HorseMake $horseMake)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HorseMake  $horseMake
     * @return \Illuminate\Http\Response
     */
    public function destroy(HorseMake $horseMake)
    {
        foreach ($horseMake->horse_models as $model) {
            $model->delete();
        }
        $horseMake->delete();
        Session::flash('success','Horse Make & Models Deleted Successfully!!');
        return redirect()->back();
    }
}
