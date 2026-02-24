<?php

namespace App\Http\Controllers;

use App\Models\Criterion;
use App\Http\Requests\StoreCriterionRequest;
use App\Http\Requests\UpdateCriterionRequest;

class CriterionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('criterions.index');
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
     * @param  \App\Http\Requests\StoreCriterionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCriterionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Criterion  $criterion
     * @return \Illuminate\Http\Response
     */
    public function show(Criterion $criterion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Criterion  $criterion
     * @return \Illuminate\Http\Response
     */
    public function edit(Criterion $criterion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCriterionRequest  $request
     * @param  \App\Models\Criterion  $criterion
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCriterionRequest $request, Criterion $criterion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Criterion  $criterion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Criterion $criterion)
    {
        //
    }
}
