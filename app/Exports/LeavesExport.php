<?php

namespace App\Exports;

use App\Models\Leave;
use Carbon\Carbon;
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
        ->filterLeaves($this->filter, $this->from, $this->to, $this->search)
        ->orderBy('created_at', 'desc');
    }


    public function map($leave): array{

        $employee_name = $leave->employee ? $leave->employee->name : "";
        $employee_surname = $leave->employee ? $leave->employee->surname : "";
        $employee_full_name = trim($employee_name." ".$employee_surname);

        $flags = collect([
            $leave->is_backdated ? 'Backdated' : null,
            $leave->is_emergency ? 'Emergency' : null,
        ])->filter()->implode(', ');

        $status = ($leave->status == 'approved') ? 'Approved' : (($leave->status == 'rejected') ? 'Rejected' : 'Pending');

        $today = Carbon::today();
        $start = $leave->from ? Carbon::parse($leave->from) : null;
        $end = $leave->to ? Carbon::parse($leave->to) : null;
        $progress = '';
        if ($start && $end) {
            if ($today->lt($start)) {
                $progress = 'Not Yet Started';
            } elseif ($today->between($start, $end)) {
                $progress = 'In Progress';
            } else {
                $progress = 'Completed';
            }
        }

            return   [
                $employee_full_name,
                $leave->department ? $leave->department->name : "",
                $leave->leave_type ? $leave->leave_type->name : "",
                $leave->created_at ? Carbon::parse($leave->created_at)->format('d F Y') : "",
                $start ? $start->format('d F Y') : "",
                $end ? $end->format('d F Y') : "",
                $leave->days ? $leave->days." Days" : "",
                $leave->reason,
                $flags,
                $status,
                $progress,
                 ];


    }
    public function headings(): array{
            return[
                'Employee',
                'Department',
                'Type',
                'Date Applied',
                'Start Date',
                'End Date',
                'Duration',
                'Reason',
                'Flags',
                'Status',
                'Progress',
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
