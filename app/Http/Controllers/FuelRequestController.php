<?php

namespace App\Http\Controllers;

use App\Models\FuelRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FuelRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('fuel_requests.index');
    }

    public function deleted()
    {
        return view('fuel_requests.deleted');
    }

    public function approved()
    {
        return view('fuel_requests.approved');
    }
    public function pending()
    {
        return view('fuel_requests.pending');
    }

    public function rejected()
    {
        return view('fuel_requests.rejected');
    }

    public function manage()
    {
        return view('fuel_requests.manage');
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FuelRequest  $fuelRequest
     * @return \Illuminate\Http\Response
     */
    public function show(FuelRequest $fuelRequest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FuelRequest  $fuelRequest
     * @return \Illuminate\Http\Response
     */
    public function edit(FuelRequest $fuelRequest)
    {
        //
    }


    public function myRequests($id){
        return view('fuel_requests.myrequests')->with([
            'id' => $id
        ]);
    }

    public function reports(){
        return view('fuel_requests.reports');
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FuelRequest  $fuelRequest
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FuelRequest $fuelRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FuelRequest  $fuelRequest
     * @return \Illuminate\Http\Response
     */
    public function destroy(FuelRequest $fuelRequest)
    {
        $fuelRequest->delete();
        Session::flash('success','Fuel Request deleted successfully');
        return redirect()->back();
    }
}
