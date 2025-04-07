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

class SalesExport implements  FromQuery,
ShouldAutoSize,
WithMapping,
WithHeadings,
WithEvents,
WithDrawings,
WithCustomStartCell
{
    use Exportable;
    public $commission;
    /**
    * @return \Illuminate\Support\Collection
    */
    public $from;
    public $to;
    public $invoice_filter;
    public $search;
    public $tax_status;
   

    public function __construct($from, $to, $invoice_filter, $tax_status, $search)
    {
    
            $this->from = $from;
            $this->to = $to;
            $this->invoice_filter = $invoice_filter;
            $this->search = $search;
            $this->tax_status = $tax_status;
           
    }
    public function query()
    { 
        if (isset($this->from) && isset($this->to)) {
            if (isset($this->search)) {
            return Invoice::query()->with(['customer:id,name','currency'])->whereBetween($this->invoice_filter,[$this->from, $this->to] )
            ->where('invoice_number','like', '%'.$this->search.'%')
            ->orWhere('status','like', '%'.$this->search.'%')
            ->orWhere('date','like', '%'.$this->search.'%')
            ->orWhere('expiry','like', '%'.$this->search.'%')
            ->orWhere('authorization','like', '%'.$this->search.'%')
            ->orWhereHas('customer', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('currency', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orderBy($this->invoice_filter,'desc');
            }else{
                if ($this->tax_status == "all") {
                    return Invoice::query()->with(['customer:id,name','currency'])->whereBetween($this->invoice_filter,[$this->from, $this->to] )
                    ->orderBy($this->invoice_filter,'desc');
                }elseif ($this->tax_status == "taxed") {
                    return Invoice::query()->with(['customer:id,name','currency'])
                    ->whereBetween($this->invoice_filter,[$this->from, $this->to] )
                    ->where('tax_amount','!=', Null)
                    ->where('tax_amount','!=', 0)
                    ->where('tax_amount','!=', "")
                    ->orderBy($this->invoice_filter,'desc');
                }elseif ($this->tax_status == "non-taxed") {
                    return Invoice::query()->with(['customer:id,name','currency'])
                    ->whereBetween($this->invoice_filter,[$this->from, $this->to] )
                    ->where('tax_amount', Null)
                    ->orWhere('tax_amount', "")
                    ->orWhere('tax_amount', 0)
                    ->orderBy($this->invoice_filter,'desc');
                }
               
            }
           
        }elseif ($this->search) {
            return Invoice::query()->with(['customer:id,name','currency'])->whereMonth($this->invoice_filter, date('m'))
            ->whereYear($this->invoice_filter, date('Y'))
            ->where('invoice_number','like', '%'.$this->search.'%')
                ->orWhere('status','like', '%'.$this->search.'%')
                ->orWhere('date','like', '%'.$this->search.'%')
                ->orWhere('expiry','like', '%'.$this->search.'%')
                ->orWhere('authorization','like', '%'.$this->search.'%')
                ->orWhereHas('customer', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('currency', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy($this->invoice_filter,'desc');
        }
        else {
            if ($this->tax_status == "all") {
                return Invoice::query()->with(['customer:id,name','currency'])->whereMonth($this->invoice_filter, date('m'))
                ->whereYear($this->invoice_filter, date('Y'))->orderBy($this->invoice_filter,'desc');
            }elseif ($this->tax_status == "taxed") {
                return Invoice::query()->with(['customer:id,name','currency'])
                ->whereMonth($this->invoice_filter, date('m'))
                ->whereYear($this->invoice_filter, date('Y'))
                ->where('tax_amount','!=', Null)
                ->where('tax_amount','!=', 0)
                ->where('tax_amount','!=', "")
                ->orderBy($this->invoice_filter,'desc');
            }elseif ($this->tax_status == "non-taxed") {
                return Invoice::query()->with(['customer:id,name','currency'])
                ->whereMonth($this->invoice_filter, date('m'))
                ->whereYear($this->invoice_filter, date('Y'))
                ->where('tax_amount', Null)
                ->orWhere('tax_amount', "")
                ->orWhere('tax_amount', 0)
                ->orderBy($this->invoice_filter,'desc');
            }
          
        }
       
       
    }


    public function map($invoice): array{

                $symbol = $invoice->currency ? $invoice->currency->symbol : "";
                $currency = $invoice->currency ? $invoice->currency->name : "";
                $subtotal =  number_format($invoice->subtotal ? $invoice->subtotal : 0,2);
                $tax_amount =    number_format($invoice->tax_amount ? $invoice->tax_amount : 0,2);
                $total =  number_format($invoice->total,2);
                if (isset($invoice->payments)){
                $payments =  number_format($invoice->payments->sum('amount'),2);
                }else{
                $payments = number_format($invoice->invoice_payments->sum('amount'),2);
                }
                $balance =  number_format($invoice->balance,2);

      
                return   [
                    $invoice->invoice_number ,
                    $invoice->customer ? $invoice->customer->name : "",
                    $invoice->date,
                    $invoice->expiry,
                    $invoice->status,
                    $currency.' '.$symbol,
                    $subtotal,
                    $tax_amount,
                    $total,
                    $payments,
                    $balance,
                     ];

    }

    public function headings(): array{
            return[
                'Invoice#',
                'Customer',
                'Date',
                'Payment Due',
                'Status',
                'Currency',
                'Subtotal',
                'Tax Amt',
                'Total',
                'Paid',
                'Balance',
            ];
    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:K7')->applyFromArray([
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
          if (file_exists(public_path('/images/uploads/'.Auth::user()->employee->company->logo))){
            $drawing->setPath(public_path('/images/uploads/'.Auth::user()->employee->company->logo));
        }else{
            $drawing->setPath(public_path('/images/uploads/logo.png'));
        }
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
