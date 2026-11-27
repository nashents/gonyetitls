<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

/** Freight > Demo Data (is_admin only) - Livewire Freight\DemoData. */
class FreightDemoDataController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()?->is_admin, 403, 'Only system administrators can manage demo data.');

        return view('freight.demo-data.index');
    }
}
