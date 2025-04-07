<?php

namespace App\Http\Controllers;

use App\Models\TrailerLink;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreTrailerLinkRequest;
use App\Http\Requests\UpdateTrailerLinkRequest;

class TrailerLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('trailer_links.index');
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
     * @param  \App\Http\Requests\StoreTrailerLinkRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTrailerLinkRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TrailerLink  $trailerLink
     * @return \Illuminate\Http\Response
     */
    public function show(TrailerLink $trailerLink)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TrailerLink  $trailerLink
     * @return \Illuminate\Http\Response
     */
    public function edit(TrailerLink $trailerLink)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTrailerLinkRequest  $request
     * @param  \App\Models\TrailerLink  $trailerLink
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTrailerLinkRequest $request, TrailerLink $trailerLink)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TrailerLink  $trailerLink
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrailerLink $trailerLink)
    {
        $trailerLink->delete();
        Session::flash('success','Trailer Link Deleted Successfully');
        return redirect()->back();
    }
}
