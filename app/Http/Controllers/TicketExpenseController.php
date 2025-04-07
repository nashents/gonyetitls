<?php

namespace App\Http\Controllers;

use App\Models\TicketExpense;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreTicketExpenseRequest;
use App\Http\Requests\UpdateTicketExpenseRequest;

class TicketExpenseController extends Controller
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
     * @param  \App\Http\Requests\StoreTicketExpenseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTicketExpenseRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TicketExpense  $ticketExpense
     * @return \Illuminate\Http\Response
     */
    public function show(TicketExpense $ticketExpense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TicketExpense  $ticketExpense
     * @return \Illuminate\Http\Response
     */
    public function edit(TicketExpense $ticketExpense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTicketExpenseRequest  $request
     * @param  \App\Models\TicketExpense  $ticketExpense
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTicketExpenseRequest $request, TicketExpense $ticketExpense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TicketExpense  $ticketExpense
     * @return \Illuminate\Http\Response
     */
    public function destroy(TicketExpense $ticketExpense)
    {
       

        $bill = $ticketExpense->bill;
        if (isset($bill)) {
            if ($bill->status == "Unpaid") {
                $bill_expenses = $bill->bill_expenses;
                if (isset($bill_expenses) && $bill_expenses->count()>0) {
                   foreach ($bill_expenses as $bill_expense) {
                        $bill_expense->delete();
                   }
                }
                $bill->delete();
        
                $ticketExpense->delete();
                Session::flash('success','Tickect Expense Deleted Successfully');
                return redirect()->back();
            }else {
                Session::flash('error','Tickect Expense Deleted Usuccessful, Bill paid already');
                return redirect()->back();
            }
       

        }else {
            $ticketExpense->delete();
            Session::flash('success','Tickect Expense Deleted Successfully');
            return redirect()->back();
        }

    }
}
