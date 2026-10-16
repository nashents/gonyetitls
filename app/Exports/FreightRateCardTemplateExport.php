<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FreightRateCardTemplateExport implements FromArray, WithHeadings
{
    use Exportable;

    // "#" example row - skipped on import (SkipsTemplateExampleRows).
    public function array(): array
    {
        return [[
            '#sell', '', 'Harare Fresh Produce (Pvt) Ltd', 'Ocean Freight', 'sea', '40ft',
            'Durban', 'Harare', 'General Cargo', 'USD', 'per_container',
            2450, 'percentage', 12, '2026-10-01', '2026-12-31', 'Example row - delete or leave, it is skipped',
        ]];
    }

    public function headings(): array
    {
        return [
            'direction', 'vendor', 'customer', 'charge_type', 'mode', 'container_type',
            'origin_location', 'destination_location', 'cargo', 'currency', 'rate_basis',
            'rate', 'markup_type', 'markup_value', 'effective_from', 'effective_to', 'notes',
        ];
    }
}
