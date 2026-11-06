<?php

namespace App\Http\Controllers;

class LossRateController extends Controller
{
    /** Trip Management > Master > Loss Rates (Livewire LossRates\Index). */
    public function index()
    {
        return view('loss_rates.index');
    }
}
