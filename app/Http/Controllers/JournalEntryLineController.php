<?php

namespace App\Http\Controllers;

use App\Models\JournalEntryLine;
use App\Http\Requests\StoreJournalEntryLineRequest;
use App\Http\Requests\UpdateJournalEntryLineRequest;

class JournalEntryLineController extends Controller
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
     * @param  \App\Http\Requests\StoreJournalEntryLineRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreJournalEntryLineRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\JournalEntryLine  $journalEntryLine
     * @return \Illuminate\Http\Response
     */
    public function show(JournalEntryLine $journalEntryLine)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JournalEntryLine  $journalEntryLine
     * @return \Illuminate\Http\Response
     */
    public function edit(JournalEntryLine $journalEntryLine)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateJournalEntryLineRequest  $request
     * @param  \App\Models\JournalEntryLine  $journalEntryLine
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateJournalEntryLineRequest $request, JournalEntryLine $journalEntryLine)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JournalEntryLine  $journalEntryLine
     * @return \Illuminate\Http\Response
     */
    public function destroy(JournalEntryLine $journalEntryLine)
    {
        //
    }
}
