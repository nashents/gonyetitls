<?php

namespace App\Http\Controllers;

use App\Models\TransporterCorridor;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreTransporterCorridorRequest;
use App\Http\Requests\UpdateTransporterCorridorRequest;

class TransporterCorridorController extends Controller
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
     * @param  \App\Http\Requests\StoreTransporterCorridorRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTransporterCorridorRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TransporterCorridor  $transporterCorridor
     * @return \Illuminate\Http\Response
     */
    public function show(TransporterCorridor $transporterCorridor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TransporterCorridor  $transporterCorridor
     * @return \Illuminate\Http\Response
     */
    public function edit(TransporterCorridor $transporterCorridor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTransporterCorridorRequest  $request
     * @param  \App\Models\TransporterCorridor  $transporterCorridor
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTransporterCorridorRequest $request, TransporterCorridor $transporterCorridor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TransporterCorridor  $transporterCorridor
     * @return \Illuminate\Http\Response
     */
    public function destroy(TransporterCorridor $transporterCorridor)
    {
        $transporterCorridor->delete();
        Session::flash('success','Corridor Deleted Successfully!!');
        return redirect()->back();
    }
}
