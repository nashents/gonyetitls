<?php

namespace App\Http\Controllers;

use App\Models\LoginAudit;
use App\Http\Requests\StoreLoginAuditRequest;
use App\Http\Requests\UpdateLoginAuditRequest;

class LoginAuditController extends Controller
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
     * @param  \App\Http\Requests\StoreLoginAuditRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLoginAuditRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LoginAudit  $loginAudit
     * @return \Illuminate\Http\Response
     */
    public function show(LoginAudit $loginAudit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LoginAudit  $loginAudit
     * @return \Illuminate\Http\Response
     */
    public function edit(LoginAudit $loginAudit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLoginAuditRequest  $request
     * @param  \App\Models\LoginAudit  $loginAudit
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLoginAuditRequest $request, LoginAudit $loginAudit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LoginAudit  $loginAudit
     * @return \Illuminate\Http\Response
     */
    public function destroy(LoginAudit $loginAudit)
    {
        //
    }
}
