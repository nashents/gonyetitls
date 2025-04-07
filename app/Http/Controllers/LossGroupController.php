<?php

namespace App\Http\Controllers;

use App\Models\LossGroup;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreLossGroupRequest;
use App\Http\Requests\UpdateLossGroupRequest;

class LossGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('loss_groups.index');
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
     * @param  \App\Http\Requests\StoreLossGroupRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLossGroupRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LossGroup  $lossGroup
     * @return \Illuminate\Http\Response
     */
    public function show(LossGroup $lossGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LossGroup  $lossGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(LossGroup $lossGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLossGroupRequest  $request
     * @param  \App\Models\LossGroup  $lossGroup
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLossGroupRequest $request, LossGroup $lossGroup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LossGroup  $lossGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(LossGroup $lossGroup)
    {
        $losses = $lossGroup->losses;
        if (isset($losses)) {
            foreach ($losses as $loss) {
                $loss->delete();
            }
        }

        $lossGroup->delete();
        Session::flash('success','Loss Cause Group Deleted Successfully');
        return redirect()->back();
    }
}
