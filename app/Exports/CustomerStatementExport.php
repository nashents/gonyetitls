<?php

namespace App\Exports;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
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

class CustomerStatementExport implements FromQuery,
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

    public $selectedType;
    public $selectedCustomer;
    public $invoices;
    public $to;
    public $from;

    public function __construct($selectedType, $selectedCustomer, $from, $to)
    {
      
            $this->selectedType = $selectedType;
            $this->selectedCustomer = $selectedCustomer;
            $this->from = $from;
            $this->to = $to;
           
    }

    public function query()
    {
        if ( isset($this->selectedCustomer) && $this->selectedType == "Outstanding Invoices") {
            return Invoice::where('customer_id', $this->selectedCustomer)
                ->where('status', 'Unpaid')
                ->orWhere('customer_id', $this->selectedCustomer)
                ->where('status', 'Partial');
    
        }elseif ( isset($this->selectedCustomer) && $this->selectedType == "Account Activity") {
            if (isset($this->from) && isset($this->to)) {
                return Invoice::query()->where('customer_id', $this->selectedCustomer)->orderBy('created_at','desc')
                ->whereBetween('created_at',[$this->from, $this->to] );
            }
          
        }
      
      
    }

    public function map($invoice): array{             

                $symbol = $invoice->currency ? $invoice->currency->symbol : "";

                return   [
                    $invoice->invoice_number ,
                    $invoice->customer ? $invoice->customer->name : "" ,
                    $invoice->date,
                    $invoice->expiry,
                    $invoice->currency ? $invoice->currency->name : "",
                    $symbol ." " . number_format($invoice->total ? $invoice->total : 0,2),
                    $symbol ." " . number_format($invoice->payments->sum('amount') ? $invoice->payments->sum('amount') : 0,2),
                    $symbol ." " . number_format($invoice->balance ? $invoice->balance : 0,2),
                    $invoice->status,
                ];

    }

    public function headings(): array{
            return[
                'Inoice#',
                'Customer',
                'Invoice Date',
                'Due Date',
                'Currency',
                'Invoice Total',
                'Amount Paid',
                'Amount Due',
                'Status',

            ];
    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:I7')->applyFromArray([
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
