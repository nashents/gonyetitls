<?php

namespace App\Exports;

use App\Models\ClearingAgent;
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

class ClearingAgentsExport implements  FromQuery,
ShouldAutoSize,
WithMapping,
WithHeadings,
WithEvents,
WithDrawings,
WithCustomStartCell
{
    use Exportable;

    public function query()
    {
        return ClearingAgent::query()->with('borders')->latest();
    }

    public function map($clearing_agent): array{
            return   [
                $clearing_agent->clearing_agent_number,
                $clearing_agent->name,
                $clearing_agent->email,
                $clearing_agent->phonenumber,
                $clearing_agent->worknumber,
                $clearing_agent->country,
                $clearing_agent->city,
                $clearing_agent->suburb,
                $clearing_agent->street_address,
                $clearing_agent->borders->pluck('name')->implode(', '),
                $clearing_agent->status == 1 ? "Active" : "Inactive",
                 ];
    }

    public function headings(): array{
            return[
                'Clearing Agent#',
                'Name',
                'Email',
                'Phonenumber',
                'Worknumber',
                'Country',
                'City',
                'Suburb',
                'Street Address',
                'Borders',
                'Status',
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
