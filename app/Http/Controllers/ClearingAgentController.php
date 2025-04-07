<?php

namespace App\Http\Controllers;

use App\Models\ClearingAgent;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreClearingAgentRequest;
use App\Http\Requests\UpdateClearingAgentRequest;

class ClearingAgentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('clearing_agents.index');
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
     * @param  \App\Http\Requests\StoreClearingAgentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreClearingAgentRequest $request)
    {
       
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ClearingAgent  $clearingAgent
     * @return \Illuminate\Http\Response
     */
    public function show(ClearingAgent $clearingAgent)
    {
        return view('clearing_agents.show')->with('clearing_agent',$clearingAgent);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ClearingAgent  $clearingAgent
     * @return \Illuminate\Http\Response
     */
    public function edit(ClearingAgent $clearingAgent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateClearingAgentRequest  $request
     * @param  \App\Models\ClearingAgent  $clearingAgent
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClearingAgentRequest $request, ClearingAgent $clearingAgent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ClearingAgent  $clearingAgent
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClearingAgent $clearingAgent)
    {
        $documents = $clearingAgent->documents;
        if (isset($documents)) {
           foreach ($documents as $document) {
                $document->delete();
           }
        }
        $contacts = $clearingAgent->contacts;
        if (isset($documents)) {
           foreach ($contacts as $contact) {
                $contact->delete();
           }
        }
        $clearingAgent->delete();
        Session::flash('success','Clearing Agent Deleted Successfully!!');
        return redirect()->back();
    }
}
