<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Payment;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('payments.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('payments.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePaymentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePaymentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function show(Payment $payment)
    {
        return view('payments.show')->with('payment',$payment);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePaymentRequest  $request
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Payment $payment)
    {

        $invoice = $payment->invoice;
        $payment_account = $payment->account;
        $customer_account = Account::find($payment->customer_account_id);
        
        if (isset($payment_account)) {
            $payment_account->balance = $payment_account->balance - $payment->amount;
            $payment_account->update();
        }
        if (isset($customer_account)) {
            $customer_account->balance = $customer_account->balance - $payment->amount;
            $customer_account->update();
        }
        if (isset($invoice)) {
            $invoice->balance = $invoice->balance + $payment->amount;
            if ($invoice->balance == $invoice->total) {
                $invoice->status = "Unpaid";
            }elseif($invoice->balance < $invoice->total && $invoice->balance > 0){
                $invoice->status = "Partial";
            }
            $invoice->update();
        }
    
        $receipt = $payment->receipt;
        
        if (isset($receipt)) {
            $receipt->delete();
        }
        $cashflow = $payment->cash_flow;
        if (isset($cashflow)) {
            $cashflow->delete();
        }
        $payment->delete();
        Session::flash('success','Payment Deleted Successfully');
        return redirect()->back();
    }
}
