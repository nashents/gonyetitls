<?php

namespace App\Http\Controllers;

use App\Models\Border;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreBorderRequest;
use App\Http\Requests\UpdateBorderRequest;

class BorderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('borders.index');
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
     * @param  \App\Http\Requests\StoreBorderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBorderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Border  $border
     * @return \Illuminate\Http\Response
     */
    public function show(Border $border)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Border  $border
     * @return \Illuminate\Http\Response
     */
    public function edit(Border $border)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBorderRequest  $request
     * @param  \App\Models\Border  $border
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBorderRequest $request, Border $border)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Border  $border
     * @return \Illuminate\Http\Response
     */
    public function destroy(Border $border)
    {
        $border->delete();
        Session::flash('success','Border Deleted Successfully!!');
        return redirect()->back();
    }
}
