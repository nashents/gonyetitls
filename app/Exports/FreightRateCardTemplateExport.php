<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FreightRateCardTemplateExport implements FromArray, WithHeadings
{
    use Exportable;

    public function array(): array
    {
        return [];
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
