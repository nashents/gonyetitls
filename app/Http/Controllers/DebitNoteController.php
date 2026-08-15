<?php

namespace App\Http\Controllers;

use App\Models\DebitNote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreDebitNoteRequest;
use App\Http\Requests\UpdateDebitNoteRequest;

class DebitNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('debit_notes.index');
    }

    public function deleted()
    {
        return view('debit_notes.deleted');
    }
    public function rejected()
    {
        return view('debit_notes.rejected');
    }
    public function pending()
    {
        return view('debit_notes.pending');
    }
    public function approved()
    {
        return view('debit_notes.approved');
    }


    public function preview($id){


        $debit_note = DebitNote::find($id);
        $debit_note_items = $debit_note->debit_note_items;
        $bill = $debit_note->bill;
        $bill_expenses = $bill ? $bill->bill_expenses : collect();
        $company = $debit_note->company;
        return view('debit_notes.preview')->with([
            'debit_note' => $debit_note,
            'bill' => $bill,
            'bill_expenses' => $bill_expenses,
            'company' => $company,
            'debit_note_items' => $debit_note_items,]);
    }

    public function print($id){
        $debit_note = DebitNote::find($id);
        $bill = $debit_note->bill;
        $bill_expenses = $bill ? $bill->bill_expenses : collect();
        $debit_note_items = $debit_note->debit_note_items;
        $company = $debit_note->company;
        return view('debit_notes.print')->with([
            'debit_note' => $debit_note,
            'bill' => $bill,
            'bill_expenses' => $bill_expenses,
            'company' => $company,
            'debit_note_items' => $debit_note_items,]);
    }

    public function generatePDF($id){
        $debit_note = DebitNote::find($id);
        $bill = $debit_note->bill;
        $bill_expenses = $bill ? $bill->bill_expenses : collect();
        $debit_note_items = $debit_note->debit_note_items;
        $company = $debit_note->company;
        $data = [
            'debit_note' => $debit_note,
            'bill' => $bill,
            'bill_expenses' => $bill_expenses,
            'company' => $company,
            'debit_note_items' => $debit_note_items,
        ];
        $pdf = PDF::loadView('debit_notes.debit_note', $data);

        return $pdf->download('debit_note.pdf');

    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('debit_notes.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreDebitNoteRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDebitNoteRequest $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DebitNote  $debitNote
     * @return \Illuminate\Http\Response
     */
    public function show(DebitNote $debitNote)
    {
        return view('debit_notes.show')->with('debit_note',$debitNote);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DebitNote  $debitNote
     * @return \Illuminate\Http\Response
     */
    public function edit(DebitNote $debitNote)
    {
        return view('debit_notes.edit')->with('debit_note',$debitNote);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDebitNoteRequest  $request
     * @param  \App\Models\DebitNote  $debitNote
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDebitNoteRequest $request, DebitNote $debitNote)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DebitNote  $debitNote
     * @return \Illuminate\Http\Response
     */
    public function destroy(DebitNote $debitNote)
    {
        $debit_note = $debitNote;
        $debit_note_items = $debit_note->debit_note_items;
        if ($debit_note_items->count()>0) {
            foreach ($debit_note_items as $debit_note_item) {
                $debit_note_item->delete();
            }
        }
        $debit_note->delete();
        Session::flash('success','Debit Note Deleted Successfully!!');
        return redirect()->back();
    }
}
