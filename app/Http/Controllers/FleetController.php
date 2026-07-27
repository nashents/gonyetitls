<?php

namespace App\Http\Controllers;

class FleetController extends Controller
{
    public function liveMap()
    {
        return view('fleet.live-map');
    }
}
