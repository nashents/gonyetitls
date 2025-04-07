<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Http\Requests\StoreSaleRequest;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\UpdateSaleRequest;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('sales.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('sales.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSaleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSaleRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function show(Sale $sale)
    {
        return view('sales.show')->with([
            'sale' => $sale
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function edit(Sale $sale)
    {
        return view('sales.edit')->with([
            'sale' => $sale
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSaleRequest  $request
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSaleRequest $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sale $sale)
    {
        
        $invoice = $sale->invoice;
        if (isset($invoice)) {
            $invoice->delete();
        }

        $sale_items = $sale->sale_items;
        if ($sale_items->count()>0) {
            foreach ($sale_items as $sale_item) {
                $sale_item->delete();
            }
        }
        $payments = $sale->payments;
        if ($payments->count()>0) {
            foreach ($payments as $payment) {
                if ($payment) {
                    $cashflow = $payment->cash_flow;

                    if (isset($cashflow)) {
                        $cashflow->delete();
                    }

                    $denominations = $payment->denominations;
                    if (isset($denominations)) {
                        foreach ($denominations as $denomination) {
                            $denomination->delete();
                        }
                    }
                    $receipt = $payment->receipt;
                    if (isset($receipt)) {
                        $receipt->delete();
                    }
                }
                $payment->delete();
            }
        }
        $sale->delete();
        Session::flash('success','Sale Deleted Successfully!!');
        return redirect()->back();
    }
}
