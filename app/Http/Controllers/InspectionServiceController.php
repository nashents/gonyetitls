<?php

namespace App\Http\Controllers;

use App\Models\InspectionService;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreInspectionServiceRequest;
use App\Http\Requests\UpdateInspectionServiceRequest;

class InspectionServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('inspection_services.index');
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
     * @param  \App\Http\Requests\StoreInspectionServiceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInspectionServiceRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InspectionService  $inspectionService
     * @return \Illuminate\Http\Response
     */
    public function show(InspectionService $inspectionService)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InspectionService  $inspectionService
     * @return \Illuminate\Http\Response
     */
    public function edit(InspectionService $inspectionService)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInspectionServiceRequest  $request
     * @param  \App\Models\InspectionService  $inspectionService
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInspectionServiceRequest $request, InspectionService $inspectionService)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InspectionService  $inspectionService
     * @return \Illuminate\Http\Response
     */
    public function destroy(InspectionService $inspectionService)
    {
        $inspectionService->delete();
        Session::flash('success','Inspection Item Removed from Checklist Successfully');
        return redirect()->back();
    }
}
