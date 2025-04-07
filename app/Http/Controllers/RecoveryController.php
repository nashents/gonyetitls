<?php

namespace App\Http\Controllers;

use App\Models\Recovery;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreRecoveryRequest;
use App\Http\Requests\UpdateRecoveryRequest;

class RecoveryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('recoveries.index');
    }
    public function rejected()
    {
        return view('recoveries.rejected');
    }
    public function pending()
    {
        return view('recoveries.pending');
    }
    public function approved()
    {
        return view('recoveries.approved');
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('recoveries.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreRecoveryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRecoveryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Recovery  $recovery
     * @return \Illuminate\Http\Response
     */
    public function show(Recovery $recovery)
    {
        return view('recoveries.show')->with('recovery', $recovery);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Recovery  $recovery
     * @return \Illuminate\Http\Response
     */
    public function edit(Recovery $recovery)
    {
        return view('recoveries.edit')->with('recovery', $recovery);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRecoveryRequest  $request
     * @param  \App\Models\Recovery  $recovery
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRecoveryRequest $request, Recovery $recovery)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Recovery  $recovery
     * @return \Illuminate\Http\Response
     */
    public function destroy(Recovery $recovery)
    {
        $payments = $recovery->payments;
        if (isset($payments)) {
            foreach ($payments as $payment) {
                $payment->delete();
            }
        }
        $documents = $recovery->documents;
        if (isset($documents)) {
            foreach ($documents as $document) {
                $document->delete();
            }
        }       
        $recovery->delete();
        Session::flash('success','Recovery Deleted Successfully!!');
        return redirect()->back();
    }
}
