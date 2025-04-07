<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HorseDocument;
use Illuminate\Support\Facades\Session;

class HorseDocumentController extends Controller
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
     * @param  \App\Models\HorseDocument  $horseDocument
     * @return \Illuminate\Http\Response
     */
    public function show(HorseDocument $horseDocument)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HorseDocument  $horseDocument
     * @return \Illuminate\Http\Response
     */
    public function edit(HorseDocument $horseDocument)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HorseDocument  $horseDocument
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HorseDocument $horseDocument)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HorseDocument  $horseDocument
     * @return \Illuminate\Http\Response
     */
    public function destroy(HorseDocument $horseDocument)
    {
        $horseDocument->delete();
        Session::flash('success','Document Deleted Successfully!!');
        return redirect()->back();
    }
}
