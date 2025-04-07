<?php

namespace App\Http\Controllers;

use App\Models\Fitness;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FitnessController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('fitnesses.index');
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
     * @param  \App\Models\Fitness  $fitness
     * @return \Illuminate\Http\Response
     */
    public function show(Fitness $fitness)
    {
        return view('fitnesses.show')->with('fitness',$fitness);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Fitness  $fitness
     * @return \Illuminate\Http\Response
     */
    public function edit(Fitness $fitness)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Fitness  $fitness
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Fitness $fitness)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Fitness  $fitness
     * @return \Illuminate\Http\Response
     */
    public function destroy(Fitness $fitness)
    {
        $fitness->delete();
        Session::flash('success','Reminder Deleted Successfully!!');
        return redirect()->back();
    }
}
