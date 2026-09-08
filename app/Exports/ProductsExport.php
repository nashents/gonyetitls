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

class ProductsExport implements  FromQuery,
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
        return Product::query()
            ->with(['tyres', 'inventories', 'assets'])
            ->where('department',$this->department)->where('buy',true)->orderBy('name','asc');
    }

    private function itemsInInventory($product)
    {
        switch ($this->department) {
            case 'tyre':
                return $product->tyres->where('status', 1)->count();
            case 'inventory':
                return $product->inventories->where('status', 1)->where('qty', '>', 0)->where('balance', '>', 0)->sum('balance');
            case 'asset':
                return $product->assets->where('status', 1)->where('qty', '>', 0)->where('balance', '>', 0)->sum('balance');
            default:
                return 0;
        }
    }

    private function totalValue($product)
    {
        $company = Auth::user()->employee->company ?? Auth::user()->company ?? null;
        $currency_id = $company ? $company->currency_id : null;

        $relations = [
            'tyre' => 'tyres',
            'inventory' => 'inventories',
            'asset' => 'assets',
        ];

        if (!isset($relations[$this->department])) {
            return 0;
        }

        $items = $product->{$relations[$this->department]};

        $value = $items->where('status', 1)
            ->where('balance', '>', 0)
            ->where('currency_id', $currency_id)
            ->filter(fn($item) => !is_null($item->total) || !is_null($item->subtotal_incl))
            ->map(fn($item) => ($item->amount ?? 0) * ($item->balance ?? 1))
            ->sum();

        $value_exchange = $items->where('status', 1)
            ->where('balance', '>', 0)
            ->where('currency_id', '!=', $currency_id)
            ->filter(fn($item) => !is_null($item->exchange_amount))
            ->map(fn($item) => ($item->exchange_amount ?? 0) * ($item->balance ?? 1))
            ->sum();

        return (is_numeric($value) && is_numeric($value_exchange)) ? $value + $value_exchange : 0;
    }

    public function map($product): array{

            return   [
                $product->name,
                $product->product_number,
                $product->identification_number,
                $this->itemsInInventory($product),
                $product->unit_of_measure,
                $this->totalValue($product),
                 ];


    }
    public function headings(): array{
            return[
                'Name',
                'Code',
                'ID/Part#',
                'Item(s) in Inventory',
                'UOM',
                'Total Value',
            ];


    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:F7')->applyFromArray([
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
