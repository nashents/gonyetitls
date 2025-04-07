<?php

namespace App\Http\Controllers;

use App\Models\CreditNote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreCreditNoteRequest;
use App\Http\Requests\UpdateCreditNoteRequest;

class CreditNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('credit_notes.index');
    }

    public function deleted()
    {
        return view('credit_notes.deleted');
    }
    public function rejected()
    {
        return view('credit_notes.rejected');
    }
    public function pending()
    {
        return view('credit_notes.pending');
    }
    public function approved()
    {
        return view('credit_notes.approved');
    }

    
    public function preview($id){
    

        $credit_note = CreditNote::find($id);
        $credit_note_items = $credit_note->credit_note_items;
        $invoice = $credit_note->invoice;
        $invoice_items = $invoice->invoice_items;
        $company = $credit_note->company;
        return view('credit_notes.preview')->with([
            'credit_note' => $credit_note,
            'invoice' => $invoice,
            'invoice_items' => $invoice_items,
            'company' => $company,
            'credit_note_items' => $credit_note_items,]);
    }

    public function print($id){
        $credit_note = CreditNote::find($id);
        $invoice = $credit_note->invoice;
        $invoice_items = $invoice->invoice_items;
        $credit_note_items = $credit_note->credit_note_items;
        $company = $credit_note->company;
        return view('credit_notes.print')->with([
            'credit_note' => $credit_note,
            'invoice' => $invoice,
            'invoice_items' => $invoice_items,
            'company' => $company,
            'credit_note_items' => $credit_note_items,]);
    }

    public function generatePDF($id){
      $credit_note = CreditNote::find($id);
        $data = [
            'credit_note' => $credit_note,
            'credit_note_items' => $credit_note->credit_note_items,
            'company' =>  $company = $credit_note->company
        ];
        $pdf = PDF::loadView('credit_notes.credit_note', $data);

        return $pdf->download('credit_note.pdf');

    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('credit_notes.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCreditNoteRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCreditNoteRequest $request)
    {
       
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CreditNote  $creditNote
     * @return \Illuminate\Http\Response
     */
    public function show(CreditNote $creditNote)
    {
        return view('credit_notes.show')->with('credit_note',$creditNote);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CreditNote  $creditNote
     * @return \Illuminate\Http\Response
     */
    public function edit(CreditNote $creditNote)
    {
        return view('credit_notes.edit')->with('credit_note',$creditNote);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCreditNoteRequest  $request
     * @param  \App\Models\CreditNote  $creditNote
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCreditNoteRequest $request, CreditNote $creditNote)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CreditNote  $creditNote
     * @return \Illuminate\Http\Response
     */
    public function destroy(CreditNote $creditNote)
    {
        $credit_note = $creditNote;
        $credit_note_items = $credit_note->credit_note_items;
        if ($credit_note_items->count()>0) {
            foreach ($credit_note_items as $credit_note_item) {
                $credit_note_item->delete();
            }
        }
        $payments = $credit_note->payments;
        if (isset($payments)) {
        if ($payments->count()>0) {
            foreach ($payments as $payment) {
                $payment->delete();
            }
        }
    }
        $credit_note->delete();
        Session::flash('success','Credit Note Deleted Successfully!!');
        return redirect()->back();
    }
}
