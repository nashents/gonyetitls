<?php

namespace App\Exports;

use App\Models\Requisition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class RequisitionExport implements FromQuery,
ShouldAutoSize,
WithMapping,
WithHeadings,
WithEvents,
WithDrawings,
WithCustomStartCell
{
    use Exportable;
 
    /**
    * @return \Illuminate\Support\Collection
    */
    public $filters;

    /**
     * @param array $filters Requisition::scopeListFilter filters, as on the Index screen
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        return Requisition::query()
            ->with(['user', 'employee.departments', 'requisition_items.expense', 'currency', 'purchase', 'purchase_orders'])
            ->listFilter(Auth::user(), $this->filters);
    }


    public function map($requisition): array{

                  
                    $name = $requisition->user ? $requisition->user->name : "";
                    $surname = $requisition->user ? $requisition->user->surname : "";
                    $user = $name." ".$surname;
                    $department = $requisition->employee && $requisition->employee->departments->first() ? $requisition->employee->departments->first()->name : "";
                    $employee_name = $requisition->employee ? $requisition->employee->name : "";
                    $employee_surname = $requisition->employee ? $requisition->employee->surname : "";
                    $employee =  $employee_name." ".  $employee_surname;
                    $symbol = $requisition->currency ? $requisition->currency->symbol : "";
                    $currency = $requisition->currency ? $requisition->currency->name : "";
                    $total =  number_format($requisition->total ?? 0,2);
                    $paid =  number_format($requisition->paid ?? 0,2);

                    if ($requisition->requisition_items) {
                        foreach ($requisition->requisition_items as $item) {
                                $name = $item->expense ? $item->expense->name : "";
                                $items[] = $name;
                        }
                        if (isset($items)) {
                            $items_list = implode(', ',$items);
                        }else {
                            $items_list = "";
                        }
                    }else{
                        $items_list = "";
                    }
           

         
               
      
                $isPo = $requisition->type == 'po_requisition';
                $po_status = "";
                if ($isPo) {
                    $po_numbers = collect([$requisition->purchase])->merge($requisition->purchase_orders)
                        ->filter()->unique('id')->pluck('purchase_number')->implode(', ');
                    $po_status = $requisition->hasPurchaseOrder() ? 'PO Attached ('.$po_numbers.')' : 'Awaiting PO';
                }

                return   [
                    $requisition->requisition_number ,
                    $isPo ? 'PO Requisition' : 'Payment Requisition',
                    $po_status,
                    $user ,
                    $employee ,
                    $department ,
                    $items_list ,
                    $requisition->description,
                    $requisition->date,
                    $currency.' '.$symbol,
                    $total,
                    $requisition->status,
                 
                 
                     ];

    }

    public function headings(): array{
            return[
                'Requisition#',
                'Type',
                'PO Status',
                'CreatedBy',
                'Requested By',
                'Department',
                'Item(s)',
                'Notes',
                'Date',
                'Currency',
                'Total',
                'Status',
            ];
    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:L7')->applyFromArray([
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
        if (file_exists(public_path('/images/uploads/'.Auth::user()->employee->company->logo))){
            $drawing->setPath(public_path('/images/uploads/'.Auth::user()->employee->company->logo));
        }else{
            $drawing->setPath(public_path('/images/uploads/logo.png'));
        }
        } 
        $drawing->setHeight(90);
        $drawing->setCoordinates('A2');
        return $drawing;
    }

    public function startCell(): string{
        return 'A7';
    }
}
