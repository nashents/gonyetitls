<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CashFlowController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('cashflows.index');
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CashFlow  $cashFlow
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cashFlow = Cashflow::find($id);
        return view('cashflows.show')->with('cashflow', $cashFlow);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CashFlow  $cashFlow
     * @return \Illuminate\Http\Response
     */
    public function edit(CashFlow $cashFlow)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CashFlow  $cashFlow
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CashFlow $cashFlow)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CashFlow  $cashFlow
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $cashFlow = CashFlow::find($id);
        $cashFlow->delete();
        Session::flash('success','Transaction Deleted Successfully!!');
        return redirect()->back();
    }
}
