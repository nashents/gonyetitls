<?php

namespace App\Http\Controllers;

use App\Models\IncomeStream;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreIncomeStreamRequest;
use App\Http\Requests\UpdateIncomeStreamRequest;

class IncomeStreamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('income_streams.index');
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
     * @param  \App\Http\Requests\StoreIncomeStreamRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreIncomeStreamRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\IncomeStream  $incomeStream
     * @return \Illuminate\Http\Response
     */
    public function show(IncomeStream $incomeStream)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\IncomeStream  $incomeStream
     * @return \Illuminate\Http\Response
     */
    public function edit(IncomeStream $incomeStream)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateIncomeStreamRequest  $request
     * @param  \App\Models\IncomeStream  $incomeStream
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateIncomeStreamRequest $request, IncomeStream $incomeStream)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\IncomeStream  $incomeStream
     * @return \Illuminate\Http\Response
     */
    public function destroy(IncomeStream $incomeStream)
    {
        $incomeStream->delete();
        Session::flash('success','Income Stream Deleted Successfully');
        return redirect()->back();
    }
}
