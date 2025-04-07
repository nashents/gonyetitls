<?php

namespace App\Http\Controllers;

use App\Models\ChecklistResult;
use App\Http\Requests\StoreChecklistResultRequest;
use App\Http\Requests\UpdateChecklistResultRequest;

class ChecklistResultController extends Controller
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
     * @param  \App\Http\Requests\StoreChecklistResultRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreChecklistResultRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ChecklistResult  $checklistResult
     * @return \Illuminate\Http\Response
     */
    public function show(ChecklistResult $checklistResult)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ChecklistResult  $checklistResult
     * @return \Illuminate\Http\Response
     */
    public function edit(ChecklistResult $checklistResult)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateChecklistResultRequest  $request
     * @param  \App\Models\ChecklistResult  $checklistResult
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateChecklistResultRequest $request, ChecklistResult $checklistResult)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ChecklistResult  $checklistResult
     * @return \Illuminate\Http\Response
     */
    public function destroy(ChecklistResult $checklistResult)
    {
        //
    }
}
