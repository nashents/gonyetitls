<?php

namespace App\Http\Controllers;

use App\Models\TrainingRequirement;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreTrainingRequirementRequest;
use App\Http\Requests\UpdateTrainingRequirementRequest;

class TrainingRequirementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('training_requirements.index');
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
     * @param  \App\Http\Requests\StoreTrainingRequirementRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTrainingRequirementRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TrainingRequirement  $trainingRequirement
     * @return \Illuminate\Http\Response
     */
    public function show(TrainingRequirement $trainingRequirement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TrainingRequirement  $trainingRequirement
     * @return \Illuminate\Http\Response
     */
    public function edit(TrainingRequirement $trainingRequirement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTrainingRequirementRequest  $request
     * @param  \App\Models\TrainingRequirement  $trainingRequirement
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTrainingRequirementRequest $request, TrainingRequirement $trainingRequirement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TrainingRequirement  $trainingRequirement
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrainingRequirement $trainingRequirement)
    {
        $trainingRequirement->delete();
        Session::flash('success','Training Requirement Deleted Successfully');
        return redirect()->back();
    }
}
