<?php

namespace App\Exports;

use App\Models\Purchase;
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

class PurchasesExport implements FromQuery,
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
    public $from;
    public $to;
    public $purchase_filter;
    public $search;
    public $department;
   

    public function __construct($from, $to, $purchase_filter,$department, $search)
    {
    
            $this->from = $from;
            $this->to = $to;
            $this->purchase_filter = $purchase_filter;
            $this->search = $search;
            $this->department = $department;
          
           
    }
    public function query()
    { 
        if (isset($this->from) && isset($this->to)) {
            if (isset($this->search)) {
                if ($this->department == "asset") {
                    return Purchase::query()->with('vendor','booking','purchase_products','purchase_products.product')->whereBetween($this->purchase_filter,[$this->from, $this->to] )
                    ->where('department',$this->department)
                    ->where('purchase_number','like', '%'.$this->search.'%')
                    ->orWhere('date','like', '%'.$this->search.'%')
                    ->orWhere('description','like', '%'.$this->search.'%')
                    ->orWhereHas('vendor', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('booking', function ($query) {
                        return $query->where('booking_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('purchase_products.product', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                   
                    ->orderBy($this->purchase_filter,'desc');
                }else{
                    return Purchase::query()->with('vendor','booking','purchase_products','purchase_products.product')->where('department',$this->department)->whereBetween($this->purchase_filter,[$this->from, $this->to] )
                    ->where('department',$this->department)
                    ->where('purchase_number','like', '%'.$this->search.'%')
                    ->orWhere('date','like', '%'.$this->search.'%')
                    ->orWhere('description','like', '%'.$this->search.'%')
                    ->orWhereHas('vendor', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('booking', function ($query) {
                        return $query->where('booking_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('purchase_products.product', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                   
                    ->orderBy($this->purchase_filter,'desc');
                }
      
            }else{
                if ($this->department == "asset") {
                    return Purchase::query()->with('vendor','purchase_products','purchase_products.product')->whereBetween($this->purchase_filter,[$this->from, $this->to] )->orderBy($this->purchase_filter,'desc');
                }else{
                    return Purchase::query()->with('vendor','purchase_products','purchase_products.product')->where('department',$this->department)->whereBetween($this->purchase_filter,[$this->from, $this->to] )->orderBy($this->purchase_filter,'desc');
                }
              
               
            }
           
        }elseif ($this->search) {
            if ($this->department == "asset") {
                return Purchase::query()->with('vendor','booking','purchase_products','purchase_products.product')->whereMonth($this->purchase_filter, date('m'))
                ->whereYear($this->purchase_filter, date('Y'))
                ->where('department',$this->department)
                ->where('purchase_number','like', '%'.$this->search.'%')
                ->orWhere('date','like', '%'.$this->search.'%')
                ->orWhere('description','like', '%'.$this->search.'%')
                ->orWhereHas('booking', function ($query) {
                    return $query->where('booking_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('vendor', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('purchase_products.product', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
               
                ->orderBy($this->purchase_filter,'desc');
            }else{
                return Purchase::query()->with('vendor','booking','purchase_products','purchase_products.product')->where('department',$this->department)->whereMonth($this->purchase_filter, date('m'))
                ->whereYear($this->purchase_filter, date('Y'))
                ->where('department',$this->department)
                ->where('purchase_number','like', '%'.$this->search.'%')
                ->orWhere('date','like', '%'.$this->search.'%')
                ->orWhere('description','like', '%'.$this->search.'%')
                ->orWhereHas('booking', function ($query) {
                    return $query->where('booking_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('vendor', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('purchase_products.product', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
               
                ->orderBy($this->purchase_filter,'desc');
            }
           
        }
        else {
            if ($this->department == "asset") {
                return Purchase::query()->with('vendor','purchase_products','purchase_products.product')->whereMonth($this->purchase_filter, date('m'))
                ->whereYear($this->purchase_filter, date('Y'))->orderBy($this->purchase_filter,'desc');
            }else{
                return Purchase::query()->with('vendor','purchase_products','purchase_products.product')->where('department',$this->department)->whereMonth($this->purchase_filter, date('m'))
                ->whereYear($this->purchase_filter, date('Y'))->orderBy($this->purchase_filter,'desc');
            }
        }
       
       
    }


    public function map($purchase): array{

                  
                    $name = $purchase->user ? $purchase->user->name : "";
                    $surname = $purchase->user ? $purchase->user->surname : "";
                    $user = $name." ".$surname;
                    $department = $purchase->user->employee ? $purchase->user->employee->departments->first()->name : "";
                    $symbol = $purchase->currency ? $purchase->currency->symbol : "";
                    $currency = $purchase->currency ? $purchase->currency->name : "";
                    $subtotal =  number_format($purchase->subtotal ? $purchase->subtotal : 0,2);
                    $tax_amount =    number_format($purchase->tax_amount ? $purchase->tax_amount : 0,2);
                    $total =  number_format($purchase->total,2);
                    if ($purchase->bill) {
                    $status = $purchase->bill->status;
                    }else{
                        $status = "pending";
                    }
                    if ($purchase->bill) {
                        $payment = number_format($purchase->bill->payments->sum('amount'),2);
                    }else{
                        $payment = 0;
                    }

                if ($purchase->purchase_products) {
                    foreach ($purchase->purchase_products as $purchase_product) {
                            $brand = $purchase_product->product->brand ? $purchase_product->product->brand->name : "";
                            $name = $purchase_product->product ? $purchase_product->product->name : "";
                            $items[] = $name." ".$brand;
                    }
                    if (isset($items)) {
                        $items_list = implode(', ',$items);
                    }else {
                        $items_list = "";
                    }
                }else{
                    $items_list = "";
                }
               
      
                return   [
                    $purchase->purchase_number ,
                    $user ,
                    $department ,
                    $purchase->vendor ? $purchase->vendor->name : "",
                    $purchase->description,
                    $items_list,
                    $purchase->date,
                    $purchase->expiry,
                    $currency.' '.$symbol,
                    $subtotal ?  $subtotal : $total,
                    $tax_amount,
                    $total,
                    $payment,
                    $status,
                    
                 
                     ];

    }

    public function headings(): array{
            return[
                'Purchase#',
                'CreatedBy',
                'Department',
                'Vendor',
                'Description',
                'Item(s)',
                'Date',
                'Expiry',
                'Currency',
                'Subtotal',
                'Tax Amt',
                'Total',
                'Paid',
                'Status',
               
               
            ];
    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:N7')->applyFromArray([
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
