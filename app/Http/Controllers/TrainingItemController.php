<?php

namespace App\Http\Controllers;

use App\Models\TrainingItem;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreTrainingItemRequest;
use App\Http\Requests\UpdateTrainingItemRequest;

class TrainingItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('training_items.index');
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
     * @param  \App\Http\Requests\StoreTrainingItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTrainingItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TrainingItem  $trainingItem
     * @return \Illuminate\Http\Response
     */
    public function show(TrainingItem $trainingItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TrainingItem  $trainingItem
     * @return \Illuminate\Http\Response
     */
    public function edit(TrainingItem $trainingItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTrainingItemRequest  $request
     * @param  \App\Models\TrainingItem  $trainingItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTrainingItemRequest $request, TrainingItem $trainingItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TrainingItem  $trainingItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrainingItem $trainingItem)
    {
        $trainingItem->delete();
        Session::flash('success','Training Item Deleted Successfully');
        return redirect()->back();
    }
}
