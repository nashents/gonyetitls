<?php

namespace App\Http\Controllers;

use App\Models\TransporterCargo;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreTransporterCargoRequest;
use App\Http\Requests\UpdateTransporterCargoRequest;

class TransporterCargoController extends Controller
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
     * @param  \App\Http\Requests\StoreTransporterCargoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTransporterCargoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TransporterCargo  $transporterCargo
     * @return \Illuminate\Http\Response
     */
    public function show(TransporterCargo $transporterCargo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TransporterCargo  $transporterCargo
     * @return \Illuminate\Http\Response
     */
    public function edit(TransporterCargo $transporterCargo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTransporterCargoRequest  $request
     * @param  \App\Models\TransporterCargo  $transporterCargo
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTransporterCargoRequest $request, TransporterCargo $transporterCargo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TransporterCargo  $transporterCargo
     * @return \Illuminate\Http\Response
     */
    public function destroy(TransporterCargo $transporterCargo)
    {
        $transporterCargo->delete();
        Session::flash('success','Cargo Deleted Successfully!!');
        return redirect()->back();
    }
}
