<?php

namespace App\Http\Controllers;

use App\Models\ProblemCategory;
use App\Http\Requests\StoreProblemCategoryRequest;
use App\Http\Requests\UpdateProblemCategoryRequest;

class ProblemCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('problem_categories.index');
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
     * @param  \App\Http\Requests\StoreProblemCategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProblemCategoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProblemCategory  $problemCategory
     * @return \Illuminate\Http\Response
     */
    public function show(ProblemCategory $problemCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProblemCategory  $problemCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(ProblemCategory $problemCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProblemCategoryRequest  $request
     * @param  \App\Models\ProblemCategory  $problemCategory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProblemCategoryRequest $request, ProblemCategory $problemCategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProblemCategory  $problemCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProblemCategory $problemCategory)
    {
        //
    }
}
