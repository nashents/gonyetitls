<?php

namespace App\Http\Controllers;

use App\Models\LossCategory;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreLossCategoryRequest;
use App\Http\Requests\UpdateLossCategoryRequest;

class LossCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('loss_categories.index');
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
     * @param  \App\Http\Requests\StoreLossCategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLossCategoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LossCategory  $lossCategory
     * @return \Illuminate\Http\Response
     */
    public function show(LossCategory $lossCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LossCategory  $lossCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(LossCategory $lossCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLossCategoryRequest  $request
     * @param  \App\Models\LossCategory  $lossCategory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLossCategoryRequest $request, LossCategory $lossCategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LossCategory  $lossCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(LossCategory $lossCategory)
    {
        $loss_groups = $lossCategory->loss_groups;
        if (isset($loss_groups)) {
            foreach ($loss_groups as $loss_group) {
                $loss_group->delete();
            }
        }
        
        $losses = $lossCategory->losses;
        if (isset($losses)) {
            foreach ($losses as $loss) {
                $loss->delete();
            }
        }
        
        $lossCategory->delete();
        Session::flash('success','Loss Cause Category Deleted Successfully');
        return redirect()->back();
    }
}
