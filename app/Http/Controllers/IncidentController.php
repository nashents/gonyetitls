<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;

class IncidentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('incidents.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('incidents.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreIncidentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreIncidentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Incident  $incident
     * @return \Illuminate\Http\Response
     */
    public function show(Incident $incident)
    {
        return view('incidents.show')->with('incident',$incident);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Incident  $incident
     * @return \Illuminate\Http\Response
     */
    public function edit(Incident $incident)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateIncidentRequest  $request
     * @param  \App\Models\Incident  $incident
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateIncidentRequest $request, Incident $incident)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Incident  $incident
     * @return \Illuminate\Http\Response
     */
    public function destroy(Incident $incident)
    {
        $incident_damages = $incident->incident_damages;
        $incident_dates = $incident->incident_dates;
        $incident_injuries = $incident->incident_injuries;
        $incident_others = $incident->incident_other;
        $documents = $incident->documents;
        $contacts = $incident->contacts;
        $immediate_causes = $incident->immediate_causes;
        $basic_causes = $incident->basic_causes;

        if (isset($basic_causes)) {
            foreach ($basic_causes as $cause) {
                $cause->delete();
            }
        }
        if (isset($immediate_causes)) {
            foreach ($immediate_causes as $cause) {
                $cause->delete();
            }
        }
        if (isset($incident_damages)) {
            foreach ($incident_damages as $damage) {
                $damage->delete();
            }
        }
        if (isset($incident_dates)) {
            foreach ($incident_dates as $date) {
                $date->delete();
            }
        }
        if (isset($incident_injuries)) {
            foreach ($incident_injuries as $injury) {
                $injury->delete();
            }
        }
        if (isset($incident_others)) {
            foreach ($incident_others as $other) {
                $other->delete();
            }
        }
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

        $incident->delete();
        Session::flash('success','Incident Deleted Successfully!!');
        return redirect()->back();
    }
}
