<?php

namespace App\Http\Controllers;

use App\Models\InspectionGroup;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreInspectionGroupRequest;
use App\Http\Requests\UpdateInspectionGroupRequest;

class InspectionGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('inspection_groups.index');
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
     * @param  \App\Http\Requests\StoreInspectionGroupRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInspectionGroupRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InspectionGroup  $inspectionGroup
     * @return \Illuminate\Http\Response
     */
    public function show(InspectionGroup $inspectionGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InspectionGroup  $inspectionGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(InspectionGroup $inspectionGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInspectionGroupRequest  $request
     * @param  \App\Models\InspectionGroup  $inspectionGroup
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInspectionGroupRequest $request, InspectionGroup $inspectionGroup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InspectionGroup  $inspectionGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(InspectionGroup $inspectionGroup)
    {
        foreach ($inspectionGroup->inspection_types as $inspection_type) {
            $inspection_type->delete();
        }
        $inspectionGroup->delete();
        Session::flash('success','Inspection Group & Types deleted successfully!!');
        return redirect()->back();

    }
}
