<?php

namespace App\Http\Controllers;

use App\Models\Loss;
use App\Http\Requests\StoreLossRequest;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\UpdateLossRequest;

class LossController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('losses.index');
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
     * @param  \App\Http\Requests\StoreLossRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLossRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Loss  $loss
     * @return \Illuminate\Http\Response
     */
    public function show(Loss $loss)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Loss  $loss
     * @return \Illuminate\Http\Response
     */
    public function edit(Loss $loss)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLossRequest  $request
     * @param  \App\Models\Loss  $loss
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLossRequest $request, Loss $loss)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Loss  $loss
     * @return \Illuminate\Http\Response
     */
    public function destroy(Loss $loss)
    {
        $loss->delete();
        Session::flash('success','Loss Cause Deleted Successfully');
        return redirect()->back();
        
    }
}
