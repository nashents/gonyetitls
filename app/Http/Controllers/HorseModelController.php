<?php

namespace App\Http\Controllers;

use App\Models\HorseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class HorseModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('horse_models.index');
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
     * @param  \App\Models\HorseModel  $horseModel
     * @return \Illuminate\Http\Response
     */
    public function show(HorseModel $horseModel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HorseModel  $horseModel
     * @return \Illuminate\Http\Response
     */
    public function edit(HorseModel $horseModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HorseModel  $horseModel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HorseModel $horseModel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HorseModel  $horseModel
     * @return \Illuminate\Http\Response
     */
    public function destroy(HorseModel $horseModel)
    {
        $horseModel->delete();
        Session::flash('success','Horse Model Deleted Successfully!!');
        return redirect()->back();
    }
}
