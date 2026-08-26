<?php

namespace App\Http\Controllers;

use App\Models\Consolidation;

class ConsolidationController extends Controller
{
    public function index()
    {
        return view('freight.consolidations.index');
    }

    public function create()
    {
        return view('freight.consolidations.create');
    }

    public function show(Consolidation $consolidation)
    {
        return view('freight.consolidations.show')->with([
            'consolidation' => $consolidation,
        ]);
    }
}
