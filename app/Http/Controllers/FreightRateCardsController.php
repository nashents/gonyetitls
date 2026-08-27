<?php

namespace App\Http\Controllers;

class FreightRateCardsController extends Controller
{
    public function index()
    {
        return view('freight.settings.rate-cards');
    }
}
