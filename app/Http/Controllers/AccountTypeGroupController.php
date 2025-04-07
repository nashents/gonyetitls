<?php

namespace App\Http\Controllers;

use App\Models\AccountTypeGroup;
use App\Http\Requests\StoreAccountTypeGroupRequest;
use App\Http\Requests\UpdateAccountTypeGroupRequest;

class AccountTypeGroupController extends Controller
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
     * @param  \App\Http\Requests\StoreAccountTypeGroupRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAccountTypeGroupRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AccountTypeGroup  $accountTypeGroup
     * @return \Illuminate\Http\Response
     */
    public function show(AccountTypeGroup $accountTypeGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AccountTypeGroup  $accountTypeGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(AccountTypeGroup $accountTypeGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAccountTypeGroupRequest  $request
     * @param  \App\Models\AccountTypeGroup  $accountTypeGroup
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAccountTypeGroupRequest $request, AccountTypeGroup $accountTypeGroup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AccountTypeGroup  $accountTypeGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(AccountTypeGroup $accountTypeGroup)
    {
        //
    }
}
