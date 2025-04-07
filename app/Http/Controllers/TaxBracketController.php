<?php

namespace App\Http\Controllers;

use App\Models\TaxBracket;
use App\Http\Requests\StoreTaxBracketRequest;
use App\Http\Requests\UpdateTaxBracketRequest;

class TaxBracketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('tax_brackets.index');
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
     * @param  \App\Http\Requests\StoreTaxBracketRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTaxBracketRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TaxBracket  $taxBracket
     * @return \Illuminate\Http\Response
     */
    public function show(TaxBracket $taxBracket)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TaxBracket  $taxBracket
     * @return \Illuminate\Http\Response
     */
    public function edit(TaxBracket $taxBracket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTaxBracketRequest  $request
     * @param  \App\Models\TaxBracket  $taxBracket
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTaxBracketRequest $request, TaxBracket $taxBracket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TaxBracket  $taxBracket
     * @return \Illuminate\Http\Response
     */
    public function destroy(TaxBracket $taxBracket)
    {
        //
    }
}
