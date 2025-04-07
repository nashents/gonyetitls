<?php

namespace App\Http\Controllers;

use App\Models\TyreAssignment;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreTyreAssignmentRequest;
use App\Http\Requests\UpdateTyreAssignmentRequest;

class TyreAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('tyre_assignments.index');
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
     * @param  \App\Http\Requests\StoreTyreAssignmentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTyreAssignmentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TyreAssignment  $tyreAssignment
     * @return \Illuminate\Http\Response
     */
    public function show(TyreAssignment $tyreAssignment)
    {
        return view('tyre_assignments.show')->with('tyre_assignment', $tyreAssignment);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TyreAssignment  $tyreAssignment
     * @return \Illuminate\Http\Response
     */
    public function edit(TyreAssignment $tyreAssignment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTyreAssignmentRequest  $request
     * @param  \App\Models\TyreAssignment  $tyreAssignment
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTyreAssignmentRequest $request, TyreAssignment $tyreAssignment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TyreAssignment  $tyreAssignment
     * @return \Illuminate\Http\Response
     */
    public function destroy(TyreAssignment $tyreAssignment)
    {
       
        $tyre = $tyreAssignment->tyre;
        if (isset($tyre)) {
            $tyre->status = 1;
            $tyre->update();
        }
        $tyre_dispatch = $tyreAssignment->tyre_dispatch;

        if (isset($tyre_dispatch)) {
            $tyre_dispatch->delete();
        }
        
        $tyreAssignment->delete();
        Session::flash('success','Tyre Assignment Deleted Successfully!!');
        return redirect()->back();
    }
}
