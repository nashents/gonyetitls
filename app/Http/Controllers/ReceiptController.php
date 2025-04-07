<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class ReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('receipts.index');
    }
    public function preview($id){
        $receipt = Receipt::find($id);
        $company = $receipt->company;
        return view('receipts.preview')->with([
            'receipt' => $receipt,
            'company' => $company,
            ]);
    }

    public function print($id){
        $receipt = Receipt::find($id);
        $company = $receipt->company;
        $invoice = $receipt->invoice;
        $sale = $receipt->sale;
        if (isset($invoice)) {
            $invoice_items = $invoice->invoice_items;
        return view('receipts.print')->with([
            'receipt' => $receipt,
            'company' => $company,
            'invoice' => $invoice,
            'invoice_items' => $invoice_items ,
            ]);
        }elseif(isset($sale)){
            $sale_items = $sale->sale_items;
            return view('receipts.print')->with([
                'receipt' => $receipt,
                'company' => $company,
                'sale' => $sale,
                'sale_items' => $sale_items ,
                ]);
        }else {
          
            return view('receipts.print')->with([
                'receipt' => $receipt,
                'company' => $company,
                'invoice' => $invoice,
                'sale' => $sale,
              
                ]);
        }
       
      
      
       
    }
 

    public function generatePDF(Receipt $receipt){
        $receipt = $receipt;
        $company = $receipt->company;
        $invoice = $receipt->invoice;
        if (isset($invoice)) {
            $invoice_items = $invoice->invoice_items;
            $data = [
                'receipt' => $receipt,
                'company' => $company,
                'invoice' => $invoice,
                'invoice_items' => $invoice_items ,
            ];
        }else {
            $data = [
                'receipt' => $receipt,
                'company' => $company,
                'invoice' => $invoice,
            ];
        }
       
        $pdf = PDF::loadView('receipts.receipt', $data);

        return $pdf->download('receipt.pdf');

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
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function show(Receipt $receipt)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function edit(Receipt $receipt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Receipt $receipt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function destroy(Receipt $receipt)
    {
        $receipt->delete();
        Session::flash('success','Receipt successfully deleted');
        return redirect()->back();
    }
}
