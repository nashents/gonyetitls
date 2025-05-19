<?php

namespace App\Http\Controllers;

use App\Models\RouteExpense;
use App\Http\Requests\StoreRouteExpenseRequest;
use App\Http\Requests\UpdateRouteExpenseRequest;

class RouteExpenseController extends Controller
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
     * @param  \App\Http\Requests\StoreRouteExpenseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRouteExpenseRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RouteExpense  $routeExpense
     * @return \Illuminate\Http\Response
     */
    public function show(RouteExpense $routeExpense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RouteExpense  $routeExpense
     * @return \Illuminate\Http\Response
     */
    public function edit(RouteExpense $routeExpense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRouteExpenseRequest  $request
     * @param  \App\Models\RouteExpense  $routeExpense
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRouteExpenseRequest $request, RouteExpense $routeExpense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RouteExpense  $routeExpense
     * @return \Illuminate\Http\Response
     */
    public function destroy(RouteExpense $routeExpense)
    {
        //
    }
}
