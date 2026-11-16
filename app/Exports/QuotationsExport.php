<?php

namespace App\Exports;

use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class QuotationsExport implements FromQuery,
ShouldAutoSize,
WithMapping,
WithHeadings,
WithEvents,
WithDrawings,
WithCustomStartCell
{
    use Exportable;

    public $filters;

    /**
     * @param array $filters Quotation::scopeListFilter filters, as on the Quotations list
     */
    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        return Quotation::query()
            ->with(['user', 'customer:id,name', 'currency', 'additional_costs'])
            ->listFilter($this->filters);
    }

    public function map($quotation): array
    {
        $user = $quotation->user ? trim($quotation->user->name.' '.$quotation->user->surname) : '';
        $currency = $quotation->currency ? $quotation->currency->name : '';

        return [
            $quotation->isSalesOrder() ? 'Sales Order' : 'Quotation',
            $quotation->quotation_number,
            $quotation->custom_ref,
            $quotation->sales_order_number,
            $quotation->sales_order_ref,
            $quotation->sales_order_date,
            $user,
            $quotation->customer ? $quotation->customer->name : '',
            $quotation->date,
            $quotation->expiry,
            $currency,
            number_format($quotation->subtotal ?? 0, 2),
            number_format($quotation->tax_amount ?? 0, 2),
            number_format($quotation->additional_costs->sum('total'), 2),
            number_format($quotation->total ?? 0, 2),
            $quotation->status == true ? 'open' : 'closed',
        ];
    }

    public function headings(): array
    {
        return [
            'Type',
            'Quotation#',
            'Quotation Ref',
            'Sales Order#',
            'Sales Order Ref',
            'Sales Order Date',
            'CreatedBy',
            'Customer',
            'Date',
            'Expiry',
            'Currency',
            'Subtotal',
            'Tax Amt',
            'Add Cost',
            'Total',
            'Status',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A7:P7')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
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
        $company = Auth::user() && Auth::user()->employee ? Auth::user()->employee->company : null;
        if ($company) {
            $drawing->setName($company->name);
            $drawing->setDescription($company->name.' Logo');
        }
        if ($company && $company->logo && file_exists(public_path('/images/uploads/'.$company->logo))) {
            $drawing->setPath(public_path('/images/uploads/'.$company->logo));
        } else {
            $drawing->setPath(public_path('/images/uploads/logo.png'));
        }
        $drawing->setHeight(90);
        $drawing->setCoordinates('A2');

        return $drawing;
    }

    public function startCell(): string
    {
        return 'A7';
    }
}
