<?php

namespace App\Exports;

use App\Models\Dispatch;
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

class DispatchesExport implements FromQuery,
ShouldAutoSize,
WithMapping,
WithHeadings,
WithEvents,
WithDrawings,
WithCustomStartCell
{
    use Exportable;

    protected $department;

    public function __construct($department = null)
    {
        $this->department = $department;
    }

    /**
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        return Dispatch::query()
            ->with([
                'user', 'employee',
                'branch', 'department', 'store', 'currency',
                'ticket', 'horse', 'vehicle', 'trailer', 'vendor',
                'dispatch_items.product', 'dispatch_items.inventory.product',
                'dispatch_items.asset.product', 'dispatch_items.tyre.product',
            ])
            ->when($this->department, function ($q) {
                $q->where('department', $this->department);
            })
            ->orderByDesc('date');
    }

    public function map($dispatch): array
    {
        $requested_by = \App\Models\Employee::find($dispatch->requested_by_id);
        $authorized_by = \App\Models\User::find($dispatch->authorized_by_id);

        $items = $dispatch->dispatch_items->map(function ($dispatch_item) {
            if ($dispatch_item->inventory) {
                return $dispatch_item->inventory->product ? $dispatch_item->inventory->product->name : "";
            } elseif ($dispatch_item->asset) {
                return $dispatch_item->asset->product ? $dispatch_item->asset->product->name : "";
            } elseif ($dispatch_item->tyre) {
                return $dispatch_item->tyre->product ? $dispatch_item->tyre->product->name : "";
            } elseif ($dispatch_item->product) {
                return $dispatch_item->product->name;
            }
            return "";
        })->filter()->implode(', ');

        return [
            $dispatch->dispatch_number,
            $dispatch->date,
            $dispatch->department,
            $dispatch->user ? trim($dispatch->user->name . ' ' . $dispatch->user->surname) : "",
            $requested_by ? trim($requested_by->name . ' ' . $requested_by->surname) : "",
            $dispatch->employee ? trim($dispatch->employee->name . ' ' . $dispatch->employee->surname) : "",
            $dispatch->branch ? $dispatch->branch->name : "",
            $dispatch->store ? $dispatch->store->name : "",
            $dispatch->ticket ? $dispatch->ticket->ticket_number : "",
            $dispatch->horse ? $dispatch->horse->registration_number : "",
            $dispatch->vehicle ? $dispatch->vehicle->registration_number : "",
            $dispatch->trailer ? $dispatch->trailer->registration_number : "",
            $dispatch->vendor ? $dispatch->vendor->name : "",
            $dispatch->description,
            $items,
            $dispatch->dispatch_items->sum('qty'),
            $dispatch->currency ? $dispatch->currency->name : "",
            $dispatch->total ? number_format((float) $dispatch->total, 2, '.', '') : '0.00',
            ucfirst($dispatch->authorization),
            $authorized_by ? trim($authorized_by->name . ' ' . $authorized_by->surname) : "",
            $dispatch->authorization_date,
            $dispatch->authorization_comments,
        ];
    }

    public function headings(): array
    {
        return [
            'Dispatch#',
            'Date',
            'Department',
            'Dispatched By',
            'Requested By',
            'Employee',
            'Branch',
            'Store',
            'Ticket#',
            'Horse',
            'Vehicle',
            'Trailer',
            'Vendor',
            'Narration',
            'Items',
            'Qty',
            'Currency',
            'Total',
            'Status',
            'Authorized By',
            'Authorized On',
            'Comments',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A7:V7')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ]
                ]);
            },
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        if (isset(Auth::user()->employee->company)) {
            $drawing->setName(Auth::user()->employee->company->name);
            $drawing->setDescription(Auth::user()->employee->company->name . 'Logo');
            if (file_exists(public_path('/images/uploads/' . Auth::user()->employee->company->logo))) {
                $drawing->setPath(public_path('/images/uploads/' . Auth::user()->employee->company->logo));
            } else {
                $drawing->setPath(public_path('/images/uploads/logo.png'));
            }
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
