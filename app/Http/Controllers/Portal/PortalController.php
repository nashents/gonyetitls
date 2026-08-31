<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\FreightJob;
use Illuminate\Support\Facades\Auth;

class PortalController extends Controller
{
    public function dashboard()
    {
        return view('portal.dashboard');
    }

    public function jobShow(FreightJob $job)
    {
        abort_unless($job->customer_id === Auth::guard('customer')->id(), 404);

        return view('portal.jobs.show', ['jobId' => $job->id]);
    }
}
