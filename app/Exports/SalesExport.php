<?php

namespace App\Exports;

use Carbon\Carbon;
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
    public $transporter_id;
    public $customer_id;
    public $currency_id;
   

    public function __construct($from, $to, $filters, $search)
    {
       
            $this->from = $from;
            $this->to = $to;
            $this->invoice_filter = $filters['invoice_filter'];
            $this->search = $search;
            $this->tax_status = $filters['tax_status'];
            $this->customer_id = $filters['customer_id'];
            $this->transporter_id = $filters['transporter_id'];
            $this->currency_id = $filters['currency_id'];
           
    }
    public function query()
    { 

        $base = Invoice::query()->with(['customer:id,name','transporter:id,name', 'currency']);

        // Always treat from/to as whole days (inclusive)
        $base->when(
                    filled($this->from) && filled($this->to),
                    fn ($q) => $q->whereBetween($this->invoice_filter, [
                        Carbon::parse($this->from)->startOfDay(),
                        Carbon::parse($this->to)->endOfDay(),
                    ]),
            fn ($q) => $q->whereMonth($this->invoice_filter, now()->month)
                        ->whereYear($this->invoice_filter, now()->year)
        );

        // Search (GROUPED)
        if (filled($this->search)) {
            $s = $this->search;

            $base->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                ->orWhere('status', 'like', "%{$s}%")
                ->orWhere('date', 'like', "%{$s}%")
                ->orWhere('expiry', 'like', "%{$s}%")
                ->orWhere('authorization', 'like', "%{$s}%")
                ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$s}%"))
                ->orWhereHas('currency', fn ($cq) => $cq->where('name', 'like', "%{$s}%"));
            });

            return $base->orderByDesc($this->invoice_filter);
        }

          if ($this->customer_id != "") {
                $base->where('customer_id', $this->customer_id);
            }
            if ($this->currency_id != "") {
                $base->where('currency_id', $this->currency_id);
            }
            if ($this->transporter_id != "") {
                $base->where('transporter_id', $this->transporter_id);
            }
        // Tax filter (GROUPED for non-taxed)
        if ($this->tax_status === 'taxed') {
            $base->whereNotNull('tax_amount')
                ->where('tax_amount', '!=', 0)
                ->where('tax_amount', '!=', '');
        } elseif ($this->tax_status === 'non-taxed') {
            $base->where(function ($q) {
                $q->whereNull('tax_amount')
                ->orWhere('tax_amount', 0)
                ->orWhere('tax_amount', '');
            });
        }

        return $base->orderByDesc($this->invoice_filter);
       
       
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
                $invoice_to = Null;
                if ($invoice->customer){
                    $invoice_to = $invoice->customer->name;
                }elseif($invoice->transporter){
                    $invoice_to = $invoice->transporter->name;
                }

                $invoice_items = $invoice->invoice_items;
                $narrations = [];

                foreach ($invoice_items as $item) {
                    $invoice_item = "";
                    if ($item->product) {
                        $parts = [];

                        if (!blank($item->product->name ?? null)) {
                            $parts[] = $item->product->name;
                        }

                        if (!blank($item->product->identification_number ?? null)) {
                            $parts[] = $item->product->identification_number;
                        }

                        if (!blank($item->inventory->serial_number ?? null)) {
                            $parts[] = $item->inventory->serial_number;
                        }

                        $invoice_item = implode(' ', $parts);

                    } elseif ($item->trip) {
                        $invoice_item = $item->trip->trip_number ?? '';
                    }

                    $narrations[] = trim($invoice_item . ' ' . ($item->description ?? '')).' '.number_format((float) $item->subtotal_incl, 2);
                }

                $narration = implode(', ', $narrations);

                $name = $invoice->user ? $invoice->user->name : "";
                $surname = $invoice->user ? $invoice->user->surname : "";
                $created_by = $name." ".$surname;
      
                return   [
                    $invoice->invoice_number ,
                    $created_by,
                    $invoice_to,
                    $narration,
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
                'CreatedBy',
                'InvoiceTo',
                'Item(s)',
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
                $event->sheet->getStyle('A7:M7')->applyFromArray([
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
