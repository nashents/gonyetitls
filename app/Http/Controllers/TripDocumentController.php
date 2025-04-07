<?php

namespace App\Http\Controllers;

use App\Models\TripDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TripDocumentController extends Controller
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
     * @param  \App\Models\TripDocument  $tripDocument
     * @return \Illuminate\Http\Response
     */
    public function show(TripDocument $tripDocument)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TripDocument  $tripDocument
     * @return \Illuminate\Http\Response
     */
    public function edit(TripDocument $tripDocument)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripDocument  $tripDocument
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TripDocument $tripDocument)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripDocument  $tripDocument
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripDocument $tripDocument)
    {
        $tripDocument->delete();
        Session::flash('success','Document Deleted Successfully!!');
        return redirect()->back();
    }
}
