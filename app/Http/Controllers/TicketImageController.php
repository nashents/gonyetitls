<?php

namespace App\Http\Controllers;

use App\Models\TicketImage;
use App\Http\Requests\StoreTicketImageRequest;
use App\Http\Requests\UpdateTicketImageRequest;

class TicketImageController extends Controller
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
     * @param  \App\Http\Requests\StoreTicketImageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTicketImageRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TicketImage  $ticketImage
     * @return \Illuminate\Http\Response
     */
    public function show(TicketImage $ticketImage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TicketImage  $ticketImage
     * @return \Illuminate\Http\Response
     */
    public function edit(TicketImage $ticketImage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTicketImageRequest  $request
     * @param  \App\Models\TicketImage  $ticketImage
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTicketImageRequest $request, TicketImage $ticketImage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TicketImage  $ticketImage
     * @return \Illuminate\Http\Response
     */
    public function destroy(TicketImage $ticketImage)
    {
        //
    }
}
