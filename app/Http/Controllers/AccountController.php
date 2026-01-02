<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('accounts.index');
    }
    public function accountsReceivable()
    {
        return view('accounts.receivable');
    }
    public function accountsPayable()
    {
        return view('accounts.payable');
    }
    public function tax()
    {
        return view('accounts.tax');
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
     * @param  \App\Http\Requests\StoreAccountRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAccountRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function show(Account $account)
    {
        return view('accounts.show')->with('account',$account);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function edit(Account $account)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAccountRequest  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAccountRequest $request, Account $account)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function destroy(Account $account)
    {
        $bill_expenses = $account->bill_expenses;
        if (isset($bill_expenses)) {
            foreach ($bill_expenses as $bill_expense) {
                $bill = $bill_expense->bill;
                if (isset($bill)) {
                  $bill->delete();
                }
                $bill_expense->delete();
              }
        }
       
        $account->delete();
        Session::flash('success','Account Deleted Successfully');
        return redirect()->back();
    }
}
