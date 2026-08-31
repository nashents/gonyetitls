<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class FreightImportController extends Controller
{
    public function rateCards()
    {
        abort_unless(Auth::user()->is_admin(), 403);

        return view('freight.imports.rate-cards');
    }

    public function jobs()
    {
        abort_unless(Auth::user()->is_admin(), 403);

        return view('freight.imports.jobs');
    }
}
