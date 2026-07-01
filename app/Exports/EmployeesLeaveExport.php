<?php

namespace App\Exports;

use App\Models\EmployeeLeave;
use Illuminate\Support\Collection;
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

class EmployeesLeaveExport implements FromQuery,
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
    public function query()
    {
        return EmployeeLeave::query()
            ->whereHas('employee', function ($query) {
                $query->where('status', 1);
            })
            ->with(['employee', 'leave_type'])
            ->orderBy('employee_id');
    }
    public function map($employeeLeave): array{

            return   [
                $employeeLeave->employee->employee_number,
                $employeeLeave->employee->name." ". $employeeLeave->employee->surname,
                $employeeLeave->leave_type->name ?? '',
                $employeeLeave->acrual_rate,
                $employeeLeave->available_leave_days,
                $employeeLeave->maximum_leave_days,
                 ];


    }
    public function headings(): array{
            return[
                'Employee#',
                'Fullname',
                'Leave Type',
                'Accrual Rate',
                'Available Leave Days',
                'Maximum Leave Days',
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
