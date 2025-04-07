<?php

namespace App\Http\Controllers;

use App\Models\CustomerContact;
use App\Http\Requests\StoreCustomerContactRequest;
use App\Http\Requests\UpdateCustomerContactRequest;

class CustomerContactController extends Controller
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
     * @param  \App\Http\Requests\StoreCustomerContactRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCustomerContactRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CustomerContact  $customerContact
     * @return \Illuminate\Http\Response
     */
    public function show(CustomerContact $customerContact)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CustomerContact  $customerContact
     * @return \Illuminate\Http\Response
     */
    public function edit(CustomerContact $customerContact)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCustomerContactRequest  $request
     * @param  \App\Models\CustomerContact  $customerContact
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCustomerContactRequest $request, CustomerContact $customerContact)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CustomerContact  $customerContact
     * @return \Illuminate\Http\Response
     */
    public function destroy(CustomerContact $customerContact)
    {
        //
    }
}
