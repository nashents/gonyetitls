<?php

namespace App\Http\Controllers;

use App\Models\TripExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TripExpenseController extends Controller
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
     * @param  \App\Models\TripExpense  $tripExpense
     * @return \Illuminate\Http\Response
     */
    public function show(TripExpense $tripExpense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TripExpense  $tripExpense
     * @return \Illuminate\Http\Response
     */
    public function edit(TripExpense $tripExpense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripExpense  $tripExpense
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TripExpense $tripExpense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripExpense  $tripExpense
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripExpense $tripExpense)
    {
        $bill = $tripExpense->bill;
        if (isset($bill)) {
            $bill_expenses = $bill->bill_expenses;
            $bill->delete();
        }
        if (isset($bill_expenses)) {
            foreach ($bill_expenses as $bill_expense) {
                $bill_expense->delete();
            }
        }
       
        $tripExpense->delete();
        Session::flash('success','Trip Expense Deleted Successfull!!');
        return redirect()->back();
    }
}
