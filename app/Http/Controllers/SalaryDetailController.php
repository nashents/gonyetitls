<?php

namespace App\Http\Controllers;

use App\Models\SalaryDetail;
use App\Http\Requests\StoreSalaryDetailRequest;
use App\Http\Requests\UpdateSalaryDetailRequest;

class SalaryDetailController extends Controller
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
     * @param  \App\Http\Requests\StoreSalaryDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSalaryDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SalaryDetail  $salaryDetail
     * @return \Illuminate\Http\Response
     */
    public function show(SalaryDetail $salaryDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalaryDetail  $salaryDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(SalaryDetail $salaryDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSalaryDetailRequest  $request
     * @param  \App\Models\SalaryDetail  $salaryDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSalaryDetailRequest $request, SalaryDetail $salaryDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalaryDetail  $salaryDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalaryDetail $salaryDetail)
    {
        //
    }
}
