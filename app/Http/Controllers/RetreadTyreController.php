<?php

namespace App\Http\Controllers;

use App\Models\RetreadTyre;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreRetreadTyreRequest;
use App\Http\Requests\UpdateRetreadTyreRequest;

class RetreadTyreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoreRetreadTyreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRetreadTyreRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RetreadTyre  $retreadTyre
     * @return \Illuminate\Http\Response
     */
    public function show(RetreadTyre $retreadTyre)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RetreadTyre  $retreadTyre
     * @return \Illuminate\Http\Response
     */
    public function edit(RetreadTyre $retreadTyre)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRetreadTyreRequest  $request
     * @param  \App\Models\RetreadTyre  $retreadTyre
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRetreadTyreRequest $request, RetreadTyre $retreadTyre)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RetreadTyre  $retreadTyre
     * @return \Illuminate\Http\Response
     */
    public function destroy(RetreadTyre $retreadTyre)
    {
        $retreadTyre->delete();
        Session::flash('success','Retread Tyre Successfully Deleted');
        return redirect()->back();
    }
}
