<?php

namespace App\Http\Controllers;

use App\Models\TaxTable;
use App\Http\Requests\StoreTaxTableRequest;
use App\Http\Requests\UpdateTaxTableRequest;

class TaxTableController extends Controller
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
     * @param  \App\Http\Requests\StoreTaxTableRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTaxTableRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TaxTable  $taxTable
     * @return \Illuminate\Http\Response
     */
    public function show(TaxTable $taxTable)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TaxTable  $taxTable
     * @return \Illuminate\Http\Response
     */
    public function edit(TaxTable $taxTable)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTaxTableRequest  $request
     * @param  \App\Models\TaxTable  $taxTable
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTaxTableRequest $request, TaxTable $taxTable)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TaxTable  $taxTable
     * @return \Illuminate\Http\Response
     */
    public function destroy(TaxTable $taxTable)
    {
        //
    }
}
