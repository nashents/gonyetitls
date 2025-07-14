<?php

namespace App\Exports;

use App\Models\Bill;
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

class VendorStatementExport implements FromQuery,
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
    public $selectedVendor;
    public $bills;
    public $to;
    public $from;

    public function __construct($selectedType, $selectedVendor, $from, $to)
    {
      
            $this->selectedType = $selectedType;
            $this->selectedVendor = $selectedVendor;
            $this->from = $from;
            $this->to = $to;
           
    }

    public function query()
    {
        if ( isset($this->selectedVendor) && $this->selectedType == "Outstanding Bills") {
            return Bill::where('vendor_id', $this->selectedVendor)
                ->where('status', 'Unpaid')
                ->orWhere('vendor_id', $this->selectedVendor)
                ->where('status', 'Partial');
    
        }elseif ( isset($this->selectedVendor) && $this->selectedType == "Account Activity") {
            if (isset($this->from) && isset($this->to)) {
                return Bill::query()->where('vendor_id', $this->selectedVendor)->orderBy('created_at','desc')
                ->whereBetween('created_at',[$this->from, $this->to] );
            }
          
        }
      
      
    }

    public function map($bill): array{             

                $symbol = $bill->currency ? $bill->currency->symbol : "";

                return   [
                    $bill->bill_number ,
                    $bill->vendor ? $bill->vendor->name : "" ,
                    $bill->bill_date,
                    $bill->due_date,
                    $bill->currency ? $bill->currency->name : "",
                    $symbol ." " . number_format($bill->total ? $bill->total : 0,2),
                    $symbol ." " . number_format($bill->payments->sum('amount') ? $bill->payments->sum('amount') : 0,2),
                    $symbol ." " . number_format($bill->balance ? $bill->balance : 0,2),
                    $bill->status,
                ];

    }

    public function headings(): array{
            return[
                'Inoice#',
                'Vendor',
                'Bill Date',
                'Due Date',
                'Currency',
                'Bill Total',
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
