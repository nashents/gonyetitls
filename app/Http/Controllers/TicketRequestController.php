<?php

namespace App\Http\Controllers;

use App\Models\TicketRequest;
use App\Http\Requests\StoreTicketRequestRequest;
use App\Http\Requests\UpdateTicketRequestRequest;

class TicketRequestController extends Controller
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
     * @param  \App\Http\Requests\StoreTicketRequestRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTicketRequestRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TicketRequest  $ticketRequest
     * @return \Illuminate\Http\Response
     */
    public function show(TicketRequest $ticketRequest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TicketRequest  $ticketRequest
     * @return \Illuminate\Http\Response
     */
    public function edit(TicketRequest $ticketRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTicketRequestRequest  $request
     * @param  \App\Models\TicketRequest  $ticketRequest
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTicketRequestRequest $request, TicketRequest $ticketRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TicketRequest  $ticketRequest
     * @return \Illuminate\Http\Response
     */
    public function destroy(TicketRequest $ticketRequest)
    {
        //
    }
}
