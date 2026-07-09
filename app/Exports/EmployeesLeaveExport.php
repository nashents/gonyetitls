<?php

namespace App\Exports;

use App\Models\EmployeeLeave;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class EmployeesLeaveExport implements FromCollection,
ShouldAutoSize,
WithMapping,
WithHeadings,
WithEvents,
WithDrawings,
WithCustomStartCell
{
    use Exportable;
    /**
    * @return Collection
    */
    public function collection()
    {
        return EmployeeLeave::query()
            ->whereHas('employee', function ($query) {
                $query->where('status', 1);
            })
            ->whereHas('leave_type', function ($query) {
                $query->where('active', true);
            })
            ->with(['employee', 'leave_type'])
            ->orderBy('employee_id')
            ->get()
            ->groupBy('employee_id')
            ->map(function ($employeeLeaves) {
                $leaveData = new RichText();
                $first = true;

                foreach ($employeeLeaves as $employeeLeave) {
                    if (!$first) {
                        $leaveData->createText("\n");
                    }
                    $first = false;

                    $boldRun = $leaveData->createTextRun($employeeLeave->leave_type->name ?? '');
                    $boldRun->getFont()->setBold(true);

                    $leaveData->createText(
                        ' - Taken:' . ($employeeLeave->days_taken ?? 0)
                        . ', Available:' . ($employeeLeave->available_leave_days ?? 0)
                        . ', Accrual:' . ($employeeLeave->acrual_rate ?? 0)
                        . ', Maximum:' . ($employeeLeave->maximum_leave_days ?? 0)
                    );
                }

                return (object) [
                    'employee' => $employeeLeaves->first()->employee,
                    'leave_data' => $leaveData,
                ];
            })
            ->values();
    }
    public function map($row): array{

            return   [
                $row->employee->employee_number,
                $row->employee->name." ". $row->employee->surname,
                $row->leave_data,
                 ];


    }
    public function headings(): array{
            return[
                'Employee#',
                'Fullname',
                'Leave Data',
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
                $highestRow = $event->sheet->getHighestRow();
                if ($highestRow >= 8) {
                    $event->sheet->getStyle('C8:C' . $highestRow)->getAlignment()->setWrapText(true);
                }
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
