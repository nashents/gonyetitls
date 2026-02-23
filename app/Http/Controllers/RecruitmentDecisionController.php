<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentDecision;
use App\Http\Requests\StoreRecruitmentDecisionRequest;
use App\Http\Requests\UpdateRecruitmentDecisionRequest;

class RecruitmentDecisionController extends Controller
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
     * @param  \App\Http\Requests\StoreRecruitmentDecisionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRecruitmentDecisionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RecruitmentDecision  $recruitmentDecision
     * @return \Illuminate\Http\Response
     */
    public function show(RecruitmentDecision $recruitmentDecision)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RecruitmentDecision  $recruitmentDecision
     * @return \Illuminate\Http\Response
     */
    public function edit(RecruitmentDecision $recruitmentDecision)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRecruitmentDecisionRequest  $request
     * @param  \App\Models\RecruitmentDecision  $recruitmentDecision
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRecruitmentDecisionRequest $request, RecruitmentDecision $recruitmentDecision)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RecruitmentDecision  $recruitmentDecision
     * @return \Illuminate\Http\Response
     */
    public function destroy(RecruitmentDecision $recruitmentDecision)
    {
        //
    }
}
