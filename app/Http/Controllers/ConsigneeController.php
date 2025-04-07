<?php

namespace App\Http\Controllers;

use App\Models\Consignee;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreConsigneeRequest;
use App\Http\Requests\UpdateConsigneeRequest;

class ConsigneeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('consignees.index');
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
     * @param  \App\Http\Requests\StoreConsigneeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreConsigneeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Consignee  $consignee
     * @return \Illuminate\Http\Response
     */
    public function show(Consignee $consignee)
    {
        return view('consignees.show')->with('consignee',$consignee);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Consignee  $consignee
     * @return \Illuminate\Http\Response
     */
    public function edit(Consignee $consignee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateConsigneeRequest  $request
     * @param  \App\Models\Consignee  $consignee
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateConsigneeRequest $request, Consignee $consignee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Consignee  $consignee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Consignee $consignee)
    {
        $documents = $consignee->documents;
        $contacts = $consignee->contacts;
        $trips = $consignee->trips;
        $offloading_points = $consignee->offloading_points;

        if (isset($documents)) {
            foreach ($documents as $document) {
               $document->delete();
            }
        }
        if (isset($contacts)) {
            foreach ($contacts as $contact) {
               $contact->delete();
            }
        }
        if (isset($trips)) {
            foreach ($trips as $trip) {
               $trip->delete();
            }
        }
        if (isset($offloading_points)) {
            foreach ($offloading_points as $offloading_point) {
               $offloading_point->delete();
            }
        }

        $consignee->delete();
        Session::flash('success','Consignee Deleted Successfully!!');
        return redirect()->back();
    }
}
