<?php

namespace App\Http\Controllers;

use App\Models\TrainingDepartment;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreTrainingDepartmentRequest;
use App\Http\Requests\UpdateTrainingDepartmentRequest;

class TrainingDepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('training_departments.index');
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
     * @param  \App\Http\Requests\StoreTrainingDepartmentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTrainingDepartmentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TrainingDepartment  $trainingDepartment
     * @return \Illuminate\Http\Response
     */
    public function show(TrainingDepartment $trainingDepartment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TrainingDepartment  $trainingDepartment
     * @return \Illuminate\Http\Response
     */
    public function edit(TrainingDepartment $trainingDepartment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTrainingDepartmentRequest  $request
     * @param  \App\Models\TrainingDepartment  $trainingDepartment
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTrainingDepartmentRequest $request, TrainingDepartment $trainingDepartment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TrainingDepartment  $trainingDepartment
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrainingDepartment $trainingDepartment)
    {
        $trainingDepartment->delete();
        Session::flash('success','Training Department Deleted Successfully');
        return redirect()->back();
    }
}
