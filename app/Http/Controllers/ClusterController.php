<?php

namespace App\Http\Controllers;

use App\Models\Cluster;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreClusterRequest;
use App\Http\Requests\UpdateClusterRequest;

class ClusterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         return view('clusters.index');
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
     * @param  \App\Http\Requests\StoreClusterRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreClusterRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cluster  $cluster
     * @return \Illuminate\Http\Response
     */
    public function show(Cluster $cluster)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Cluster  $cluster
     * @return \Illuminate\Http\Response
     */
    public function edit(Cluster $cluster)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateClusterRequest  $request
     * @param  \App\Models\Cluster  $cluster
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClusterRequest $request, Cluster $cluster)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cluster  $cluster
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cluster $cluster)
    {
        $cluster->delete();
        Session::flash('success','Cluster Deleted Successfully!! ');
        return redirect()->back();
    }
}
