<?php

namespace App\Http\Controllers;

use App\Models\TrainingPlan;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreTrainingPlanRequest;
use App\Http\Requests\UpdateTrainingPlanRequest;

class TrainingPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('training_plans.index');
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
     * @param  \App\Http\Requests\StoreTrainingPlanRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTrainingPlanRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TrainingPlan  $trainingPlan
     * @return \Illuminate\Http\Response
     */
    public function show(TrainingPlan $trainingPlan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TrainingPlan  $trainingPlan
     * @return \Illuminate\Http\Response
     */
    public function edit(TrainingPlan $trainingPlan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTrainingPlanRequest  $request
     * @param  \App\Models\TrainingPlan  $trainingPlan
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTrainingPlanRequest $request, TrainingPlan $trainingPlan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TrainingPlan  $trainingPlan
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrainingPlan $trainingPlan)
    {
        $trainingPlan->delete();
        Session::flash('success','Training Plan Deleted Successfully');
        return redirect()->back();
    }
}
