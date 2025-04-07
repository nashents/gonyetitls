<?php

namespace App\Http\Controllers;

use App\Models\TyreDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TyreDetailController extends Controller
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
     * @param  \App\Models\TyreDetail  $tyreDetail
     * @return \Illuminate\Http\Response
     */
    public function show(TyreDetail $tyreDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TyreDetail  $tyreDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(TyreDetail $tyreDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TyreDetail  $tyreDetail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TyreDetail $tyreDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TyreDetail  $tyreDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(TyreDetail $tyreDetail)
    {
        $tyreDetail->delete();
        Session::flash('success','Tyre Deleted Successfully');
        return redirect()->back();
    }
}
