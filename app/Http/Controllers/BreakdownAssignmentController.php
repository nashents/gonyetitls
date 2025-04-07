<?php

namespace App\Http\Controllers;

use App\Models\BreakdownAssignment;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreBreakdownAssignmentRequest;
use App\Http\Requests\UpdateBreakdownAssignmentRequest;

class BreakdownAssignmentController extends Controller
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
     * @param  \App\Http\Requests\StoreBreakdownAssignmentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBreakdownAssignmentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BreakdownAssignment  $breakdownAssignment
     * @return \Illuminate\Http\Response
     */
    public function show(BreakdownAssignment $breakdownAssignment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BreakdownAssignment  $breakdownAssignment
     * @return \Illuminate\Http\Response
     */
    public function edit(BreakdownAssignment $breakdownAssignment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBreakdownAssignmentRequest  $request
     * @param  \App\Models\BreakdownAssignment  $breakdownAssignment
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBreakdownAssignmentRequest $request, BreakdownAssignment $breakdownAssignment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BreakdownAssignment  $breakdownAssignment
     * @return \Illuminate\Http\Response
     */
    public function destroy(BreakdownAssignment $breakdownAssignment)
    {
        $breakdownAssignment->delete();
        Session::flash('success','Breakdown Assignment Deleted Successfully!!');
        return redirect()->back();
    }
}
