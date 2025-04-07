<?php

namespace App\Http\Controllers;

use App\Models\JobImage;
use App\Http\Requests\StoreJobImageRequest;
use App\Http\Requests\UpdateJobImageRequest;

class JobImageController extends Controller
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
     * @param  \App\Http\Requests\StoreJobImageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreJobImageRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\JobImage  $jobImage
     * @return \Illuminate\Http\Response
     */
    public function show(JobImage $jobImage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JobImage  $jobImage
     * @return \Illuminate\Http\Response
     */
    public function edit(JobImage $jobImage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateJobImageRequest  $request
     * @param  \App\Models\JobImage  $jobImage
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateJobImageRequest $request, JobImage $jobImage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JobImage  $jobImage
     * @return \Illuminate\Http\Response
     */
    public function destroy(JobImage $jobImage)
    {
        //
    }
}
