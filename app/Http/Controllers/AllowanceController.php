<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreAllowanceRequest;
use App\Http\Requests\UpdateAllowanceRequest;

class AllowanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('allowances.index');
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
     * @param  \App\Http\Requests\StoreAllowanceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAllowanceRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Allowance  $allowance
     * @return \Illuminate\Http\Response
     */
    public function show(Allowance $allowance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Allowance  $allowance
     * @return \Illuminate\Http\Response
     */
    public function edit(Allowance $allowance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAllowanceRequest  $request
     * @param  \App\Models\Allowance  $allowance
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAllowanceRequest $request, Allowance $allowance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Allowance  $allowance
     * @return \Illuminate\Http\Response
     */
    public function destroy(Allowance $allowance)
    {
        if ($allowance->default) {
            Session::flash('error','This is a default system allowance and cannot be deleted.');
            return redirect()->back();
        }

        $allowance->delete();
        Session::flash('success','Allowance Deleted Successfully');
        return redirect()->back();
    }
}
