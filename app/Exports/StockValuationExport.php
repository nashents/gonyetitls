<?php

namespace App\Exports;

use App\Models\Product;
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

class StockValuationExport implements  FromQuery,
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

    public $department;
   
   

    public function __construct($department)
    {
            $this->department = $department;
           
    }
    public function query()
    {
        return Product::query()->where('department',$this->department)->where('buy',true)->orderBy('name','asc');
    }

    public function calculateTotalValue($product)
    {
      
        if (!$product) {
            return null;
        }

        $currency_id = Auth::user()->employee->company->currency_id;
        $total_value = 0;

        $relations = [
            'tyre' => 'tyres',
            'inventory' => 'inventories',
            'asset' => 'assets'
        ];

        if (!isset($relations[$this->department])) {
            return null; // invalid department
        }

        $relation = $relations[$this->department];

        $items = $product->$relation;
        $value = $items->where('status', 1)
            ->where('balance', '>', 0)
            ->where('currency_id', $currency_id)
            ->filter(fn($item) => !is_null($item->total) || !is_null($item->subtotal_incl))
            ->map(function ($item) {
                $amount = $item->amount ?? 0;
                $qty    = $item->balance ?? 1;   // fallback qty
                return $amount * $qty;
            })
            ->sum();

        // --- DIFFERENT CURRENCY ---
        $value_exchange = $items->where('status', 1)
            ->where('balance', '>', 0)
            ->where('currency_id', '!=', $currency_id)
            ->filter(fn($item) => !is_null($item->exchange_amount))
            ->map(function ($item) {
                $exchange = $item->exchange_amount ?? 0;
                $qty      = $item->balance ?? 1;
                return $exchange * $qty;
            })
            ->sum();

        if (is_numeric($value) && is_numeric($value_exchange)) {
            $total_value = $value + $value_exchange;
        }

        return $total_value;
    }

    public function map($product): array{

            $brand = $product->brand ? $product->brand->name : "";
            $currency = Auth::user()->employee->company->currency->name;
            $symbol = Auth::user()->employee->company->currency->symbol;
            $total = $this->calculateTotalValue($product);
            $qty = Null;
            if($this->department == "tyre")
                $qty = $product->tyres->where('status',1)->count();
            elseif($this->department == "inventory"){
                $qty = $product->inventories->where('status',1)->where('balance','>',0)->count();
            }elseif($this->department == "asset"){
                $qty = $product->assets->where('status',1)->where('balance','>',0)->count();
            }
                
          

            return   [
                $product->name,
                $brand,
                $product->product_number,
                $product->identification_number,
                $product->unit_of_measure,
                $qty,
                $currency ." ".$symbol,
                $total
                 ];


    }
    public function headings(): array{
            return[
                'Name',
                'Brand',
                'Code',
                'Part/Model#',
                'UOM',
                'Qty',
                'Currency',
                'Total Value',
            ];


    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:H7')->applyFromArray([
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
