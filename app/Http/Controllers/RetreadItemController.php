<?php

namespace App\Http\Controllers;

use App\Models\RetreadItem;
use App\Http\Requests\StoreRetreadItemRequest;
use App\Http\Requests\UpdateRetreadItemRequest;

class RetreadItemController extends Controller
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
     * @param  \App\Http\Requests\StoreRetreadItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRetreadItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RetreadItem  $retreadItem
     * @return \Illuminate\Http\Response
     */
    public function show(RetreadItem $retreadItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RetreadItem  $retreadItem
     * @return \Illuminate\Http\Response
     */
    public function edit(RetreadItem $retreadItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRetreadItemRequest  $request
     * @param  \App\Models\RetreadItem  $retreadItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRetreadItemRequest $request, RetreadItem $retreadItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RetreadItem  $retreadItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(RetreadItem $retreadItem)
    {
        //
    }
}
