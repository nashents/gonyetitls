<?php

namespace App\Http\Controllers;

use App\Models\SalaryItem;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreSalaryItemRequest;
use App\Http\Requests\UpdateSalaryItemRequest;

class SalaryItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('salary_items.index');
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
     * @param  \App\Http\Requests\StoreSalaryItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSalaryItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SalaryItem  $salaryItem
     * @return \Illuminate\Http\Response
     */
    public function show(SalaryItem $salaryItem)
    {
        return view('salary_items.show')->with('salary_item', $salaryItem);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalaryItem  $salaryItem
     * @return \Illuminate\Http\Response
     */
    public function edit(SalaryItem $salaryItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSalaryItemRequest  $request
     * @param  \App\Models\SalaryItem  $salaryItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSalaryItemRequest $request, SalaryItem $salaryItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalaryItem  $salaryItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalaryItem $salaryItem)
    {
        $salaryItem->delete();
        Session::flash('success','Salary Item Deleted Successfully!!');
        return redirect()->back();
    }
}
