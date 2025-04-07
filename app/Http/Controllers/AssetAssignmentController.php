<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetAssignment;
use Illuminate\Support\Facades\Session;

class AssetAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('asset_assignments.index');
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
     * @param  \App\Models\AssetAssignment  $assetAssignment
     * @return \Illuminate\Http\Response
     */
    public function show(AssetAssignment $assetAssignment)
    {
        return view('asset_assignments.show')->with('asset_assignment', $assetAssignment);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AssetAssignment  $assetAssignment
     * @return \Illuminate\Http\Response
     */
    public function edit(AssetAssignment $assetAssignment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AssetAssignment  $assetAssignment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AssetAssignment $assetAssignment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AssetAssignment  $assetAssignment
     * @return \Illuminate\Http\Response
     */
    public function destroy(AssetAssignment $assetAssignment)
    {
        $asset = $assetAssignment->asset;
        if (isset($asset)) {
            $asset->status = 1;
            $asset->update();
        }
      
        $assetAssignment->delete();
        Session::flash('success','Asset Assignment Deleted Successfully');
        return redirect()->back();
    }
}
