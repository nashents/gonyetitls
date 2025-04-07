<?php

namespace App\Http\Controllers;

use App\Models\AccountTransaction;
use App\Http\Requests\StoreAccountTransactionRequest;
use App\Http\Requests\UpdateAccountTransactionRequest;

class AccountTransactionController extends Controller
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
     * @param  \App\Http\Requests\StoreAccountTransactionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAccountTransactionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AccountTransaction  $accountTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(AccountTransaction $accountTransaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AccountTransaction  $accountTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(AccountTransaction $accountTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAccountTransactionRequest  $request
     * @param  \App\Models\AccountTransaction  $accountTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAccountTransactionRequest $request, AccountTransaction $accountTransaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AccountTransaction  $accountTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(AccountTransaction $accountTransaction)
    {
        //
    }
}
