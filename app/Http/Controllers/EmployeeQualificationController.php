<?php

namespace App\Http\Controllers;

use App\Models\EmployeeQualification;
use App\Http\Requests\StoreEmployeeQualificationRequest;
use App\Http\Requests\UpdateEmployeeQualificationRequest;

class EmployeeQualificationController extends Controller
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
     * @param  \App\Http\Requests\StoreEmployeeQualificationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEmployeeQualificationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EmployeeQualification  $employeeQualification
     * @return \Illuminate\Http\Response
     */
    public function show(EmployeeQualification $employeeQualification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EmployeeQualification  $employeeQualification
     * @return \Illuminate\Http\Response
     */
    public function edit(EmployeeQualification $employeeQualification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateEmployeeQualificationRequest  $request
     * @param  \App\Models\EmployeeQualification  $employeeQualification
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEmployeeQualificationRequest $request, EmployeeQualification $employeeQualification)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmployeeQualification  $employeeQualification
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeQualification $employeeQualification)
    {
        //
    }
}
