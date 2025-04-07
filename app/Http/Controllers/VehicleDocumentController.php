<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VehicleDocument;
use Illuminate\Support\Facades\Session;

class VehicleDocumentController extends Controller
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
     * @param  \App\Models\VehicleDocument  $vehicleDocument
     * @return \Illuminate\Http\Response
     */
    public function show(VehicleDocument $vehicleDocument)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VehicleDocument  $vehicleDocument
     * @return \Illuminate\Http\Response
     */
    public function edit(VehicleDocument $vehicleDocument)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VehicleDocument  $vehicleDocument
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VehicleDocument $vehicleDocument)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VehicleDocument  $vehicleDocument
     * @return \Illuminate\Http\Response
     */
    public function destroy(VehicleDocument $vehicleDocument)
    {
        $vehicleDocument->delete();
        Session::flash('success','Document Deleted Successfully!!');
        return redirect()->back();
    }
}
