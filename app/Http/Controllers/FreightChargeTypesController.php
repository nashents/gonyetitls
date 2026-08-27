<?php

namespace App\Http\Controllers;

class FreightChargeTypesController extends Controller
{
    public function index()
    {
        return view('freight.settings.charge-types');
    }
}
