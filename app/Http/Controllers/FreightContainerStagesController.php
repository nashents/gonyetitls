<?php

namespace App\Http\Controllers;

class FreightContainerStagesController extends Controller
{
    public function index()
    {
        return view('freight.settings.container-stages');
    }
}
