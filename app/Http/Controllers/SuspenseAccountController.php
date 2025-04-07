<?php

namespace App\Http\Controllers;

use App\Models\SuspenseAccount;
use App\Http\Requests\StoreSuspenseAccountRequest;
use App\Http\Requests\UpdateSuspenseAccountRequest;

class SuspenseAccountController extends Controller
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
     * @param  \App\Http\Requests\StoreSuspenseAccountRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSuspenseAccountRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SuspenseAccount  $suspenseAccount
     * @return \Illuminate\Http\Response
     */
    public function show(SuspenseAccount $suspenseAccount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SuspenseAccount  $suspenseAccount
     * @return \Illuminate\Http\Response
     */
    public function edit(SuspenseAccount $suspenseAccount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSuspenseAccountRequest  $request
     * @param  \App\Models\SuspenseAccount  $suspenseAccount
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSuspenseAccountRequest $request, SuspenseAccount $suspenseAccount)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SuspenseAccount  $suspenseAccount
     * @return \Illuminate\Http\Response
     */
    public function destroy(SuspenseAccount $suspenseAccount)
    {
        //
    }
}
