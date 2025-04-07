<?php

namespace App\Http\Controllers;

use App\Models\TrailerImage;
use App\Http\Requests\StoreTrailerImageRequest;
use App\Http\Requests\UpdateTrailerImageRequest;

class TrailerImageController extends Controller
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
     * @param  \App\Http\Requests\StoreTrailerImageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTrailerImageRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TrailerImage  $trailerImage
     * @return \Illuminate\Http\Response
     */
    public function show(TrailerImage $trailerImage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TrailerImage  $trailerImage
     * @return \Illuminate\Http\Response
     */
    public function edit(TrailerImage $trailerImage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTrailerImageRequest  $request
     * @param  \App\Models\TrailerImage  $trailerImage
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTrailerImageRequest $request, TrailerImage $trailerImage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TrailerImage  $trailerImage
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrailerImage $trailerImage)
    {
        //
    }
}
