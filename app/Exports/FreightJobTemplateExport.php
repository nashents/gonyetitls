<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FreightJobTemplateExport implements FromArray, WithHeadings
{
    use Exportable;

    // "#" example row - skipped on import (SkipsTemplateExampleRows).
    public function array(): array
    {
        return [[
            '#Harare Fresh Produce (Pvt) Ltd', 'Freight Forwarding & Customs Clearing', 'USD', 'South Africa', 'Zimbabwe',
            'road', 'draft', 'PO-4471', 'import', 'FCL',
            'Durban', 'Harare', 'FOB', '2026-10-16', 'Durban', 'Beira',
            'BKG-88213', 'Agricultural machinery spares', 12500, 38.5, 24, 'Example row - delete or leave, it is skipped',
        ]];
    }

    public function headings(): array
    {
        return [
            'customer', 'freight_service_type', 'currency', 'origin_country', 'destination_country',
            'primary_transport_mode', 'status', 'customer_reference', 'import_export_type', 'shipment_type',
            'origin', 'destination', 'incoterm', 'opened_at', 'port_of_loading', 'port_of_discharge',
            'booking_reference', 'cargo_description', 'gross_weight', 'volume_cbm', 'package_count', 'notes',
        ];
    }
}
