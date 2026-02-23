<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentCandidate;
use App\Http\Requests\StoreRecruitmentCandidateRequest;
use App\Http\Requests\UpdateRecruitmentCandidateRequest;

class RecruitmentCandidateController extends Controller
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
     * @param  \App\Http\Requests\StoreRecruitmentCandidateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRecruitmentCandidateRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RecruitmentCandidate  $recruitmentCandidate
     * @return \Illuminate\Http\Response
     */
    public function show(RecruitmentCandidate $recruitmentCandidate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RecruitmentCandidate  $recruitmentCandidate
     * @return \Illuminate\Http\Response
     */
    public function edit(RecruitmentCandidate $recruitmentCandidate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRecruitmentCandidateRequest  $request
     * @param  \App\Models\RecruitmentCandidate  $recruitmentCandidate
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRecruitmentCandidateRequest $request, RecruitmentCandidate $recruitmentCandidate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RecruitmentCandidate  $recruitmentCandidate
     * @return \Illuminate\Http\Response
     */
    public function destroy(RecruitmentCandidate $recruitmentCandidate)
    {
        //
    }
}
