<?php

namespace App\Http\Controllers;

class FleetController extends Controller
{
    public function liveMap()
    {
        return view('fleet.live-map');
    }

    public function ezyTrackDeviceMappings()
    {
        return view('fleet.ezytrack-device-mappings');
    }
}
