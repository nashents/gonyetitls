<?php

namespace App\Http\Controllers;

use App\Models\Consignor;
use App\Models\Trip;
use App\Models\TransportOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreConsignorRequest;
use App\Http\Requests\UpdateConsignorRequest;

class ConsignorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('consignors.index');
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
     * @param  \App\Http\Requests\StoreConsignorRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreConsignorRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Consignor  $consignor
     * @return \Illuminate\Http\Response
     */
    public function show(Consignor $consignor)
    {
        return view('consignors.show')->with('consignor',$consignor);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Consignor  $consignor
     * @return \Illuminate\Http\Response
     */
    public function edit(Consignor $consignor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateConsignorRequest  $request
     * @param  \App\Models\Consignor  $consignor
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateConsignorRequest $request, Consignor $consignor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Consignor  $consignor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Consignor $consignor)
    {
        DB::transaction(function () use ($consignor) {
            foreach ($consignor->documents as $document) {
                $document->delete();
            }
            foreach ($consignor->contacts as $contact) {
                $contact->delete();
            }
            // Trips and transport orders are financial records: detach the
            // consignor instead of deleting them.
            Trip::where('consignor_id', $consignor->id)->update(['consignor_id' => null]);
            TransportOrder::where('consignor_id', $consignor->id)->update(['consignor_id' => null]);

            $consignor->delete();
        });
        Session::flash('success','Consignor Deleted Successfully!!');
        return redirect()->back();
    }
}
