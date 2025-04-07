<?php

namespace App\Http\Controllers;

use App\Models\Transporter;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreTransporterRequest;
use App\Http\Requests\UpdateTransporterRequest;

class TransporterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('transporters.index');
    }
    public function rejected()
    {
        return view('transporters.rejected');
    }
    public function pending()
    {
        return view('transporters.pending');
    }
    public function approved()
    {
        return view('transporters.approved');
    }
    
    public function deleted()
    {
        return view('transporters.deleted');
    }


    public function reports(){
        return view('transporters.reports');
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
     * @param  \App\Http\Requests\StoreTransporterRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTransporterRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transporter  $transporter
     * @return \Illuminate\Http\Response
     */
    public function show(Transporter $transporter)
    {
        return view('transporters.show')->with('transporter', $transporter);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transporter  $transporter
     * @return \Illuminate\Http\Response
     */
    public function edit(Transporter $transporter)
    {
        return view('transporters.edit')->with('transporter', $transporter);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTransporterRequest  $request
     * @param  \App\Models\Transporter  $transporter
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTransporterRequest $request, Transporter $transporter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transporter  $transporter
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transporter $transporter)
    {
        
        $transporter->delete();
        Session::flash('success','Transporter Successfully Deleted');
        return redirect()->route('transporters.index');
    }
}
