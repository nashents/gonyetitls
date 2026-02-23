<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentScore;
use App\Http\Requests\StoreRecruitmentScoreRequest;
use App\Http\Requests\UpdateRecruitmentScoreRequest;

class RecruitmentScoreController extends Controller
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
     * @param  \App\Http\Requests\StoreRecruitmentScoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRecruitmentScoreRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RecruitmentScore  $recruitmentScore
     * @return \Illuminate\Http\Response
     */
    public function show(RecruitmentScore $recruitmentScore)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RecruitmentScore  $recruitmentScore
     * @return \Illuminate\Http\Response
     */
    public function edit(RecruitmentScore $recruitmentScore)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRecruitmentScoreRequest  $request
     * @param  \App\Models\RecruitmentScore  $recruitmentScore
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRecruitmentScoreRequest $request, RecruitmentScore $recruitmentScore)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RecruitmentScore  $recruitmentScore
     * @return \Illuminate\Http\Response
     */
    public function destroy(RecruitmentScore $recruitmentScore)
    {
        //
    }
}
