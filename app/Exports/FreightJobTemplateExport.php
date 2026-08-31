<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FreightJobTemplateExport implements FromArray, WithHeadings
{
    use Exportable;

    public function array(): array
    {
        return [];
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
