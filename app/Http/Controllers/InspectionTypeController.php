<?php

namespace App\Http\Controllers;

use App\Models\InspectionType;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreInspectionTypeRequest;
use App\Http\Requests\UpdateInspectionTypeRequest;

class InspectionTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('inspection_types.index');
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
     * @param  \App\Http\Requests\StoreInspectionTypeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInspectionTypeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InspectionType  $inspectionType
     * @return \Illuminate\Http\Response
     */
    public function show(InspectionType $inspectionType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InspectionType  $inspectionType
     * @return \Illuminate\Http\Response
     */
    public function edit(InspectionType $inspectionType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInspectionTypeRequest  $request
     * @param  \App\Models\InspectionType  $inspectionType
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInspectionTypeRequest $request, InspectionType $inspectionType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InspectionType  $inspectionType
     * @return \Illuminate\Http\Response
     */
    public function destroy(InspectionType $inspectionType)
    {
        $inspectionType->delete();
        Session::flash('success','Inspection Type Successfully Deleted');
        return redirect()->back();
    }
}
