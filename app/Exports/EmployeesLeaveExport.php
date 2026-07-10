<?php

namespace App\Exports;

use App\Models\EmployeeLeave;
use App\Models\LeaveType;
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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
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

    protected Collection $leaveTypes;

    public function __construct()
    {
        $this->leaveTypes = LeaveType::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    /**
    * @return Collection
    */
    public function collection()
    {
        return EmployeeLeave::query()
            ->whereHas('employee', function ($query) {
                $query->where('status', 1);
            })
            ->whereIn('leave_type_id', $this->leaveTypes->pluck('id'))
            ->with(['employee', 'leave_type'])
            ->orderBy('employee_id')
            ->get()
            ->groupBy('employee_id')
            ->map(function ($employeeLeaves) {
                $leaveDataByType = $employeeLeaves->keyBy('leave_type_id')->map(function ($employeeLeave) {
                    return 'Taken:' . ($employeeLeave->days_taken ?? 0)
                        . ', Available:' . ($employeeLeave->available_leave_days ?? 0)
                        . ', Maximum:' . ($employeeLeave->maximum_leave_days ?? 0);
                });

                return (object) [
                    'employee' => $employeeLeaves->first()->employee,
                    'leave_data' => $leaveDataByType,
                ];
            })
            ->values();
    }
    public function map($row): array{

            $data = [
                $row->employee->employee_number,
                $row->employee->name." ". $row->employee->surname,
            ];

            foreach ($this->leaveTypes as $leaveType) {
                $data[] = $row->leave_data->get($leaveType->id, '');
            }

            return $data;


    }
    public function headings(): array{
            return array_merge(
                ['Employee#', 'Fullname'],
                $this->leaveTypes->pluck('name')->toArray()
            );


    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $lastColumn = Coordinate::stringFromColumnIndex($this->leaveTypes->count() + 2);
                $event->sheet->getStyle("A7:{$lastColumn}7")->applyFromArray([
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
