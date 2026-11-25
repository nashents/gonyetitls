<?php

namespace App\Http\Controllers;

use App\Exports\ConsolidationManifestExport;
use App\Models\Consolidation;
use App\Services\Freight\ConsolidationManifestBuilder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

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

    /** Bulk-create client (house) jobs straight into this consolidation. */
    public function addClients(Consolidation $consolidation)
    {
        return view('freight.consolidations.add-clients')->with([
            'consolidation' => $consolidation,
        ]);
    }

    /** Printable LCL cargo manifest for the consolidation's container. */
    public function manifest(Consolidation $consolidation, ConsolidationManifestBuilder $builder)
    {
        return view('freight.consolidations.manifest', $builder->build($consolidation) + [
            'consolidation' => $consolidation,
            'company' => Auth::user()->employee->company ?? Auth::user()->company ?? null,
        ]);
    }

    public function manifestExcel(Consolidation $consolidation, ConsolidationManifestBuilder $builder)
    {
        return Excel::download(
            new ConsolidationManifestExport($builder->build($consolidation)),
            'manifest-' . $consolidation->consolidation_number . '.xlsx'
        );
    }
}
