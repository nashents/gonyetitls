<?php

namespace App\Http\Controllers;

class FreightShippingLinesController extends Controller
{
    public function index()
    {
        return view('freight.settings.shipping-lines');
    }
}
