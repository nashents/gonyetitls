<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('purchases.index');
    }
    public function manage()
    {
        return view('purchases.manage');
    }

    public function rejected()
    {
        return view('purchases.rejected');
    }
    public function pending()
    {
        return view('purchases.pending');
    }
    public function approved()
    {
        return view('purchases.approved');
    }
    public function deleted()
    {
        return view('purchases.deleted');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('purchases.create');
    }

    public function preview($id){
        $purchase = Purchase::find($id);
        $company = $purchase->user->employee->company;
        $purchase_products = $purchase->purchase_products;
        return view('purchases.preview')->with([
            'purchase' => $purchase,
            'company' => $company,
            'purchase_products' => $purchase_products,
            ]);
    }

    public function print($id){
        $purchase = Purchase::find($id);
        $company = $purchase->user->employee->company;
        $purchase_products = $purchase->purchase_products;
        return view('purchases.print')->with([
            'purchase' => $purchase,
            'company' => $company,
            'purchase_products' => $purchase_products,

        ]);
    }

    public function generatePDF(Purchase $purchase){
        $company = $purchase->user->employee->company;
        $purchase_products = $purchase->purchase_products;
        $data = [
            'purchase' => $purchase,
            'company' => $company,
            'purchase_products' => $purchase_products,
        ];
        $pdf = PDF::loadView('purchases.purchase', $data);

        return $pdf->download('purchase.pdf');

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
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function show(Purchase $purchase)
    {

        return view('purchases.show')->with('purchase', $purchase);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function edit(Purchase $purchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Purchase $purchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(Purchase $purchase)
    {
        $purchase->purchase_products()->delete();
        $purchase->inventories()->delete();

        if ($purchase->bill) {
            $purchase->bill->bill_expenses()->delete();
            $purchase->bill()->delete();
        }
       
        $purchase->delete();
        Session::flash('success','Purchase Order Deleted Successfully!!');
        return redirect()->back();
    }
}
