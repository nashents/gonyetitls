<?php

namespace App\Http\Controllers;

use App\Models\GatePass;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreGatePassRequest;
use App\Http\Requests\UpdateGatePassRequest;

class GatePassController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('gate_passes.index');
    }

    public function rejected($department = null)
    {
        return view('gate_passes.rejected')->with('department',$department);
    }
    public function pending($department = null)
    {
        return view('gate_passes.pending')->with('department',$department);
    }
    public function approved($department = null)
    {
        return view('gate_passes.approved')->with('department',$department);
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
     * @param  \App\Http\Requests\StoreGatePassRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreGatePassRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GatePass  $gatePass
     * @return \Illuminate\Http\Response
     */
    public function show(GatePass $gatePass)
    {
        return view('gate_passes.show')->with('gate_pass',$gatePass);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GatePass  $gatePass
     * @return \Illuminate\Http\Response
     */
    public function edit(GatePass $gatePass)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateGatePassRequest  $request
     * @param  \App\Models\GatePass  $gatePass
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGatePassRequest $request, GatePass $gatePass)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GatePass  $gatePass
     * @return \Illuminate\Http\Response
     */
    public function destroy(GatePass $gatePass)
    {
        $gatePass->delete();
        Session::flash('success','Gate Pass Deleted Successfully');
        return redirect()->back();
    }
}
