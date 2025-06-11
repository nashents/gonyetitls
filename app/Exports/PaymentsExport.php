<?php

namespace App\Exports;

use App\Models\Payment;
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

class PaymentsExport implements  FromQuery,
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
    public $payment_filter;
    public $search;
    public $movement;
   

    public function __construct($from, $to, $payment_filter, $movement, $search)
    {
    
            $this->from = $from;
            $this->to = $to;
            $this->payment_filter = $payment_filter;
            $this->search = $search;
            $this->movement = $movement;
           
    }
    public function query()
    { 
        if (isset($this->from) && isset($this->to)) {
            if (isset($this->search)) {
            return Payment::query()->with(['customer:id,name','currency'])->whereBetween($this->payment_filter,[$this->from, $this->to] )
            ->where('payment_number','like', '%'.$this->search.'%')
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
            ->orderBy($this->payment_filter,'desc');
            }else{
                if ($this->movement == "all") {
                    return Payment::query()->with(['customer:id,name','currency'])->whereBetween($this->payment_filter,[$this->from, $this->to] )
                    ->orderBy($this->payment_filter,'desc');
                }elseif ($this->movement == "taxed") {
                    return Payment::query()->with(['customer:id,name','currency'])
                    ->whereBetween($this->payment_filter,[$this->from, $this->to] )
                    ->where('tax_amount','!=', Null)
                    ->where('tax_amount','!=', 0)
                    ->where('tax_amount','!=', "")
                    ->orderBy($this->payment_filter,'desc');
                }elseif ($this->movement == "non-taxed") {
                    return Payment::query()->with(['customer:id,name','currency'])
                    ->whereBetween($this->payment_filter,[$this->from, $this->to] )
                    ->where('tax_amount', Null)
                    ->orWhere('tax_amount', "")
                    ->orWhere('tax_amount', 0)
                    ->orderBy($this->payment_filter,'desc');
                }
               
            }
           
        }elseif ($this->search) {
            return Payment::query()->with(['customer:id,name','currency'])->whereMonth($this->payment_filter, date('m'))
            ->whereYear($this->payment_filter, date('Y'))
            ->where('payment_number','like', '%'.$this->search.'%')
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
                ->orderBy($this->payment_filter,'desc');
        }
        else {
            if ($this->movement == "all") {
                return Payment::query()->with(['customer:id,name','currency'])->whereMonth($this->payment_filter, date('m'))
                ->whereYear($this->payment_filter, date('Y'))->orderBy($this->payment_filter,'desc');
            }elseif ($this->movement == "taxed") {
                return Payment::query()->with(['customer:id,name','currency'])
                ->whereMonth($this->payment_filter, date('m'))
                ->whereYear($this->payment_filter, date('Y'))
                ->where('tax_amount','!=', Null)
                ->where('tax_amount','!=', 0)
                ->where('tax_amount','!=', "")
                ->orderBy($this->payment_filter,'desc');
            }elseif ($this->movement == "non-taxed") {
                return Payment::query()->with(['customer:id,name','currency'])
                ->whereMonth($this->payment_filter, date('m'))
                ->whereYear($this->payment_filter, date('Y'))
                ->where('tax_amount', Null)
                ->orWhere('tax_amount', "")
                ->orWhere('tax_amount', 0)
                ->orderBy($this->payment_filter,'desc');
            }
          
        }
       
       
    }


    public function map($payment): array{

                $symbol = $payment->currency ? $payment->currency->symbol : "";
                $currency = $payment->currency ? $payment->currency->name : "";
                $subtotal =  number_format($payment->subtotal ? $payment->subtotal : 0,2);
                $tax_amount =    number_format($payment->tax_amount ? $payment->tax_amount : 0,2);
                $total =  number_format($payment->total,2);
                if (isset($payment->payments)){
                $payments =  number_format($payment->payments->sum('amount'),2);
                }else{
                $payments = number_format($payment->payment_payments->sum('amount'),2);
                }
                $balance =  number_format($payment->balance,2);

      
                return   [
                    $payment->payment_number ,
                    $payment->customer ? $payment->customer->name : "",
                    $payment->date,
                    $payment->expiry,
                    $payment->status,
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
                'Payment#',
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
