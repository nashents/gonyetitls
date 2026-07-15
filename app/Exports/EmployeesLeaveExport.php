<?php

namespace App\Exports;

use App\Models\EmployeeLeave;
use App\Models\Leave;
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
use PhpOffice\PhpSpreadsheet\Style\Alignment;
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
        $daysTaken = Leave::query()
            ->whereYear('from', date('Y'))
            ->where('hod_decision', 'approved')
            ->where('management_decision', 'approved')
            ->whereIn('leave_type_id', $this->leaveTypes->pluck('id'))
            ->selectRaw('employee_id, leave_type_id, SUM(days) as total_days')
            ->groupBy('employee_id', 'leave_type_id')
            ->get()
            ->groupBy('employee_id');

        return EmployeeLeave::query()
            ->whereHas('employee', function ($query) {
                $query->where('status', 1);
            })
            ->whereIn('leave_type_id', $this->leaveTypes->pluck('id'))
            ->with(['employee', 'leave_type'])
            ->orderBy('employee_id')
            ->get()
            ->groupBy('employee_id')
            ->map(function ($employeeLeaves, $employeeId) use ($daysTaken) {
                $takenByType = $daysTaken->get($employeeId, collect())->keyBy('leave_type_id');

                $leaveDataByType = $employeeLeaves->keyBy('leave_type_id')->map(function ($employeeLeave) use ($takenByType) {
                    return (object) [
                        'taken' => (float) ($takenByType->get($employeeLeave->leave_type_id)->total_days ?? 0),
                        'available' => $employeeLeave->available_leave_days ?? 0,
                        'maximum' => $employeeLeave->maximum_leave_days ?? 0,
                    ];
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
                $leave = $row->leave_data->get($leaveType->id);
                $data[] = $leave->taken ?? '';
                $data[] = $leave->available ?? '';
                $data[] = $leave->maximum ?? '';
            }

            return $data;


    }
    public function headings(): array{
            $typeRow = ['Employee#', 'Fullname'];
            $subRow = ['', ''];

            foreach ($this->leaveTypes as $leaveType) {
                $typeRow[] = $leaveType->name;
                $typeRow[] = '';
                $typeRow[] = '';
                $subRow[] = 'Taken';
                $subRow[] = 'Available';
                $subRow[] = 'Maximum';
            }

            return [$typeRow, $subRow];


    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $lastColumn = Coordinate::stringFromColumnIndex($this->leaveTypes->count() * 3 + 2);

                $event->sheet->mergeCells("A7:A8");
                $event->sheet->mergeCells("B7:B8");

                foreach ($this->leaveTypes as $index => $leaveType) {
                    $startCol = 3 + ($index * 3);
                    $endCol = $startCol + 2;
                    $startLetter = Coordinate::stringFromColumnIndex($startCol);
                    $endLetter = Coordinate::stringFromColumnIndex($endCol);
                    $event->sheet->mergeCells("{$startLetter}7:{$endLetter}7");
                }

                $event->sheet->getStyle("A7:{$lastColumn}8")->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
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
