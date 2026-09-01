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

    public function fanTrackerDeviceMappings()
    {
        return view('fleet.fantracker-device-mappings');
    }

    public function pinpointDeviceMappings()
    {
        return view('fleet.pinpoint-device-mappings');
    }
}
