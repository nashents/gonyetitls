<?php

namespace App\Http\Controllers;

use App\Models\Compliance;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreComplianceRequest;
use App\Http\Requests\UpdateComplianceRequest;

class ComplianceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('compliances.index');
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
     * @param  \App\Http\Requests\StoreComplianceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreComplianceRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Compliance  $compliance
     * @return \Illuminate\Http\Response
     */
    public function show(Compliance $compliance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Compliance  $compliance
     * @return \Illuminate\Http\Response
     */
    public function edit(Compliance $compliance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateComplianceRequest  $request
     * @param  \App\Models\Compliance  $compliance
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateComplianceRequest $request, Compliance $compliance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Compliance  $compliance
     * @return \Illuminate\Http\Response
     */
    public function destroy(Compliance $compliance)
    {
        $compliance->delete();
        Session::flash('success','Compliance Deleted Successfully!!');
        return redirect()->back();
    }
}
