<?php

namespace App\Exports;

use App\Models\Border;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class BordersExport implements  FromQuery,
ShouldAutoSize,
WithMapping,
WithHeadings,
WithEvents,
WithDrawings,
WithCustomStartCell
{
    use Exportable;

    private $countries;

    public function query()
    {
        return Border::query()->orderBy('name', 'asc');
    }

    private function countryName($id)
    {
        if (!isset($this->countries)) {
            $this->countries = Country::pluck('name', 'id');
        }

        return $id ? ($this->countries[$id] ?? "") : "";
    }

    public function map($border): array{
            return   [
                $border->name,
                $this->countryName($border->country_a),
                $this->countryName($border->country_b),
                 ];
    }

    public function headings(): array{
            return[
                'Name',
                'Country A',
                'Country B',
            ];
    }

    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:C7')->applyFromArray([
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
        $company = Auth::user()->employee->company ?? Auth::user()->company;
        $logo = isset($company) ? public_path('/images/uploads/'.$company->logo) : null;
        if (isset($company)) {
            $drawing->setName($company->name);
            $drawing->setDescription($company->name . 'Logo');
        }
        $drawing->setPath($logo && is_file($logo) ? $logo : public_path('/images/uploads/logo.png'));
        $drawing->setHeight(90);
        $drawing->setCoordinates('A2');

        return $drawing;
    }

    public function startCell(): string{
        return 'A7';
    }
}
