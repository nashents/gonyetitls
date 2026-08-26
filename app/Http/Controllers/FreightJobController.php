<?php

namespace App\Http\Controllers;

use App\Models\FreightJob;

class FreightJobController extends Controller
{
    public function index()
    {
        return view('freight.jobs.index');
    }

    public function create()
    {
        return view('freight.jobs.create');
    }

    public function show(FreightJob $job)
    {
        return view('freight.jobs.show')->with([
            'job' => $job,
        ]);
    }
}
