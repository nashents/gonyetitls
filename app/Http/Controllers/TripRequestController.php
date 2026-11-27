<?php

namespace App\Http\Controllers;

/** Trip Requests from the client's freight instance (Gonyeti Instance Link, transport side). */
class TripRequestController extends Controller
{
    public function index()
    {
        return view('trip_requests.index');
    }
}
