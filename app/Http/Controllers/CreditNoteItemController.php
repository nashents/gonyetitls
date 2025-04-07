<?php

namespace App\Http\Controllers;

use App\Models\CreditNoteItem;
use App\Http\Requests\StoreCreditNoteItemRequest;
use App\Http\Requests\UpdateCreditNoteItemRequest;

class CreditNoteItemController extends Controller
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
     * @param  \App\Http\Requests\StoreCreditNoteItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCreditNoteItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CreditNoteItem  $creditNoteItem
     * @return \Illuminate\Http\Response
     */
    public function show(CreditNoteItem $creditNoteItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CreditNoteItem  $creditNoteItem
     * @return \Illuminate\Http\Response
     */
    public function edit(CreditNoteItem $creditNoteItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCreditNoteItemRequest  $request
     * @param  \App\Models\CreditNoteItem  $creditNoteItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCreditNoteItemRequest $request, CreditNoteItem $creditNoteItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CreditNoteItem  $creditNoteItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(CreditNoteItem $creditNoteItem)
    {
        //
    }
}
