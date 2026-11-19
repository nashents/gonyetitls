<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('documents.index');
    }
    
    public function documents($id, $category)
    {
        return view('documents.all')->with([
            'id' =>$id,
            'category' => $category
        ]);
    }

    /** Chain of custody (hand-over / receipt log) for one document. */
    public function custody(Document $document)
    {
        return view('documents.custody')->with('document', $document);
    }

    /**
     * Printable QR label to stick on / clip to the physical document. The QR
     * opens the custody page, which jumps to whichever action the scanner can
     * take (confirm receipt if it's addressed to them, else hand over).
     */
    public function custodyLabel(Document $document)
    {
        $user = auth()->user();

        return view('documents.custody-label')->with([
            'document' => $document,
            'company' => optional($user->employee)->company ?? $user->company ?? null,
            'scanUrl' => route('documents.custody', ['document' => $document->id, 'action' => 'scan']),
        ]);
    }

    /** Printable custody log (receipt-book page) for one document. */
    public function custodyPrint(Document $document)
    {
        $service = app(\App\Services\Documents\DocumentCustodyService::class);
        $user = auth()->user();
        abort_unless($service->canView($user, $document), 403);

        $document->load('user');

        return view('documents.custody-print')->with([
            'document' => $document,
            'company' => optional($user->employee)->company ?? $user->company ?? \App\Models\Company::first(),
            'movements' => $document->movements()->with(['fromUser', 'toUser', 'receiver', 'user'])->orderBy('id')->get(),
            'holder' => $service->currentHolder($document),
            'versions' => $service->versions($document),
            'printedBy' => trim($user->name . ' ' . $user->surname),
        ]);
    }

    /** Hand-overs waiting for / sent by the logged-in user. */
    public function custodyMine()
    {
        return view('documents.custody-mine');
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
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function show(Document $document)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function edit(Document $document)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Document $document)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function destroy(Document $document)
    {
        $document->delete();
        Session::flash('success','Document Deleted Successfully !!');
        return redirect()->back();
    }
}
