<?php

namespace App\Http\Controllers;

use App\Models\VendorType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class VendorTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('vendor_types.index');
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
     * @param  \App\Models\VendorType  $vendorType
     * @return \Illuminate\Http\Response
     */
    public function show(VendorType $vendorType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VendorType  $vendorType
     * @return \Illuminate\Http\Response
     */
    public function edit(VendorType $vendorType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VendorType  $vendorType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VendorType $vendorType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VendorType  $vendorType
     * @return \Illuminate\Http\Response
     */
    public function destroy(VendorType $vendorType)
    {
        $vendorType->delete();
        Session::flash('success','Vendor Type Deleted Successfully!!');
        return redirect()->back();
    }
}
