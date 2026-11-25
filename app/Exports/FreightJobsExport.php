<?php

namespace App\Exports;

use App\Models\FreightJob;
use App\Services\Freight\FreightCostingService;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

/**
 * Freight Jobs list export (Excel / CSV / PDF), same layout as the other
 * module exports: company logo at A2, headings from A7. Takes the list's
 * on-screen filters (FreightJob::scopeListFilter), incl. the Pending /
 * Approved / Rejected page it was exported from.
 */
class FreightJobsExport implements FromQuery, ShouldAutoSize, WithMapping, WithHeadings, WithEvents, WithDrawings, WithCustomStartCell
{
    use Exportable;

    public function __construct(private array $filters = [])
    {
    }

    public function query()
    {
        return FreightJob::query()
            ->withSum(['costs as accrued_cost' => fn ($q) => $q->whereIn('verification_status', FreightCostingService::COST_ACCRUED_STATUSES)], 'exchange_amount')
            ->with([
                'currency:id,symbol,name', 'customer:id,name', 'freight_service_type:id,name', 'authorized_by:id,name,surname',
                'fromDestination.country', 'toDestination.country',
                'shipments.transport_documents', 'shipments.customs_declarations', 'shipments.containers.shipping_line', 'shipments.parties',
            ])
            ->listFilter($this->filters);
    }

    public function map($job): array
    {
        $shipment = $job->shipments->sortByDesc('id')->first();
        $masterBl = $shipment?->transport_documents
            ->whereIn('document_type', ['master_bill_of_lading', 'master_air_waybill'])->first();
        $declaration = $shipment?->customs_declarations->first();
        $containers = $shipment?->containers ?? collect();
        $consignee = $shipment?->parties->firstWhere('party_type', 'consignee')?->party()?->name;

        $place = fn ($destination) => $destination ? trim(($destination->country->name ?? '') . ' ' . $destination->city) : null;

        $revenue = (float) $job->actual_revenue;
        $cost = (float) $job->actual_cost;
        $profit = $revenue - $cost;

        return [
            $job->job_number,
            $job->created_at?->format('d M Y'),
            $job->customer_label,
            $job->customer_reference,
            $job->freight_service_type?->name,
            $job->import_export_type ? ucfirst($job->import_export_type) : null,
            $job->primary_transport_mode ? ucfirst($job->primary_transport_mode) : null,
            $place($job->fromDestination) ?? $job->origin,
            $place($job->toDestination) ?? $job->destination,
            $consignee,
            $masterBl?->document_number,
            $declaration?->declaration_number,
            $containers->first()?->shipping_line?->name,
            $containers->pluck('container_number')->filter()->implode(', '),
            $shipment?->etd?->format('d M Y'),
            $shipment?->eta?->format('d M Y'),
            ucwords(str_replace('_', ' ', (string) $job->status)),
            ucfirst((string) $job->authorization),
            $job->authorized_by ? trim($job->authorized_by->name . ' ' . $job->authorized_by->surname) : null,
            $job->authorization_date ? \Carbon\Carbon::parse($job->authorization_date)->format('d M Y') : null,
            $job->currency?->name,
            $job->estimated_revenue !== null ? number_format((float) $job->estimated_revenue, 2) : null,
            $job->estimated_cost !== null ? number_format((float) $job->estimated_cost, 2) : null,
            number_format($revenue, 2),
            number_format($cost, 2),
            number_format($profit, 2),
            $revenue != 0.0 ? round($profit / $revenue * 100, 1) . '%' : null,
            number_format((float) ($job->accrued_cost ?? 0), 2),
        ];
    }

    public function headings(): array
    {
        return [
            'Job #', 'Created', 'Customer', 'Customer Ref', 'Service', 'Import/Export', 'Mode',
            'Origin', 'Destination', 'Consignee', 'Master BL/AWB', 'Declaration #', 'Shipping Line', 'Container #s',
            'ETD', 'ETA', 'Status', 'Authorization', 'Authorized By', 'Authorized On',
            'Currency', 'Est. Revenue', 'Est. Cost', 'Revenue', 'Cost', 'Gross Profit', 'Margin %', 'Unverified Cost',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A7:AB7')->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ],
                ]);
            },
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $company = Auth::user()?->employee?->company;

        if ($company) {
            $drawing->setName($company->name);
            $drawing->setDescription($company->name . ' Logo');
        }
        $logo = $company ? public_path('/images/uploads/' . $company->logo) : null;
        $drawing->setPath($logo && file_exists($logo) ? $logo : public_path('/images/uploads/logo.png'));
        $drawing->setHeight(90);
        $drawing->setCoordinates('A2');

        return $drawing;
    }

    public function startCell(): string
    {
        return 'A7';
    }
}
