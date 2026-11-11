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
   

    public $auth_status;

    public function __construct($from, $to, $purchase_filter, $department, $search, $auth_status = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->purchase_filter = $purchase_filter ?: 'created_at';
        $this->search = $search;
        $this->department = $department;
        $this->auth_status = $auth_status;
    }

    /**
     * Same filters as the purchase orders table (Purchases\Index::render):
     * department, date range (default this month unless searching),
     * grouped search and auth status.
     */
    public function query()
    {
        $query = Purchase::query()
            ->with('vendor', 'booking', 'purchase_products', 'purchase_products.product')
            ->where('department', $this->department);

        if (filled($this->from) && filled($this->to)) {
            $query->whereBetween($this->purchase_filter, [$this->from, $this->to]);
        } elseif (blank($this->search)) {
            $query->whereYear($this->purchase_filter, now()->year)
                ->whereMonth($this->purchase_filter, now()->month);
        }

        if (filled($this->search)) {
            $search = '%' . $this->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', $search)
                    ->orWhere('date', 'like', $search)
                    ->orWhere('description', 'like', $search)
                    ->orWhereHas('vendor', fn ($sub) => $sub->where('name', 'like', $search))
                    ->orWhereHas('booking', fn ($sub) => $sub->where('booking_number', 'like', $search))
                    ->orWhereHas('purchase_products.product', fn ($sub) => $sub->where('name', 'like', $search));
            });
        }

        \App\Http\Livewire\Concerns\FiltersAuthStatus::filterByAuthStatus($query, $this->auth_status);

        return $query->orderBy($this->purchase_filter, 'desc');
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
                            // product may since have been deleted
                            $brand = optional(optional($purchase_product->product)->brand)->name ?? "";
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
