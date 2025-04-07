<?php

namespace App\Http\Controllers;

use App\Models\TrailerAssignment;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreTrailerAssignmentRequest;
use App\Http\Requests\UpdateTrailerAssignmentRequest;

class TrailerAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('trailer_assignments.index');
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
     * @param  \App\Http\Requests\StoreTrailerAssignmentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTrailerAssignmentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TrailerAssignment  $trailerAssignment
     * @return \Illuminate\Http\Response
     */
    public function show(TrailerAssignment $trailerAssignment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TrailerAssignment  $trailerAssignment
     * @return \Illuminate\Http\Response
     */
    public function edit(TrailerAssignment $trailerAssignment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTrailerAssignmentRequest  $request
     * @param  \App\Models\TrailerAssignment  $trailerAssignment
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTrailerAssignmentRequest $request, TrailerAssignment $trailerAssignment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TrailerAssignment  $trailerAssignment
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrailerAssignment $trailerAssignment)
    {
        $trailerAssignment->delete();
        Session::flash('success','Horse - Trailer Assignment Deleted Successfully!!');
        return redirect()->back();
    }
}
