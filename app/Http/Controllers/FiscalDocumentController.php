<?php

namespace App\Http\Controllers;

use App\Models\FiscalDocument;
use App\Http\Requests\StoreFiscalDocumentRequest;
use App\Http\Requests\UpdateFiscalDocumentRequest;

class FiscalDocumentController extends Controller
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
     * @param  \App\Http\Requests\StoreFiscalDocumentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFiscalDocumentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FiscalDocument  $fiscalDocument
     * @return \Illuminate\Http\Response
     */
    public function show(FiscalDocument $fiscalDocument)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FiscalDocument  $fiscalDocument
     * @return \Illuminate\Http\Response
     */
    public function edit(FiscalDocument $fiscalDocument)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateFiscalDocumentRequest  $request
     * @param  \App\Models\FiscalDocument  $fiscalDocument
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFiscalDocumentRequest $request, FiscalDocument $fiscalDocument)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FiscalDocument  $fiscalDocument
     * @return \Illuminate\Http\Response
     */
    public function destroy(FiscalDocument $fiscalDocument)
    {
        //
    }
}
