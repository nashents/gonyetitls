<?php

namespace App\Exports;

use App\Models\Leave;
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

class LeavesExport implements FromQuery,
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
    public $filter;
    public $search;

    public function __construct($from = Null, $to = Null, $filter = Null, $search = null)
    {
       $this->from = $from;
       $this->to = $to;
       $this->filter = $filter;
       $this->search = $search;
    }

    public function query()
    {
        return Leave::query()
        ->with(['employee', 'user', 'department', 'leave_type'])
        ->where('user_id', Auth::id())
        ->filterLeaves($this->filter, $this->from, $this->to, $this->search)
        ->orderBy('created_at', 'desc');
    }


    public function map($leave): array{

        $employee_name = $leave->employee ? $leave->employee->name : "";
        $employee_surname = $leave->employee ? $leave->employee->surname : "";
        $employee_full_name = $employee_name ." ".$employee_surname;
        $user_name = $leave->user ? $leave->user->name : "";
        $user_surname = $leave->user ? $leave->user->surname : "";
        $user_full_name = $user_name ." ".$user_surname;

            return   [
                $user_full_name ,
                $leave->created_at ,
                $employee_full_name ,
                $leave->from,
                $leave->to,
                $leave->days,
                $leave->reason,
                $leave->status,
                 ];


    }
    public function headings(): array{
            return[
                'CreatedBy',
                'AppliedOn',
                'Employee',
                'Start Date',
                'End Date',
                'Duration',
                'Reason',
                'Status',
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
