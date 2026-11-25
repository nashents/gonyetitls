<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;

/** Excel copy of the LCL manifest (same data as the printable page). */
class ConsolidationManifestExport implements FromView, ShouldAutoSize, WithTitle
{
    public function __construct(private array $manifest)
    {
    }

    public function view(): View
    {
        return view('exports.consolidation-manifest', $this->manifest);
    }

    public function title(): string
    {
        return 'Manifest';
    }
}
