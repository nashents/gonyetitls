<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\AttendanceRegister;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AttendanceRegisterExport implements
    FromQuery,
    ShouldAutoSize,
    WithMapping,
    WithHeadings,
    WithEvents,
    WithDrawings,
    WithCustomStartCell,
    WithColumnWidths
{
    use Exportable;

    public $from;
    public $to;
    public $attendance_filter;
    public $department_id;
    public $search;

    public $totals;
    public $notesTotals;

    public function __construct($from = null, $to = null, $department_id = null, $search = null, $attendance_filter)
    {
        $this->from = $from;
        $this->attendance_filter = $attendance_filter;
        $this->to = $to;
        $this->department_id = $department_id;
        $this->search = $search;

        $base = $this->query();

        // Status counts (grouped on attendance_registers.status but filtered through Attendance)
        $summary = (clone $base)
            ->select([
                'status',
                DB::raw('COUNT(*) as total'),
            ])
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $this->totals = (object) [
            'total_marked'  => array_sum($summary),
            'status_counts' => $summary,
        ];

        // Notes buckets
        $notePatterns = [
            'Early Arrival' => ['early arrival', 'early'],
            'Late Arrival'  => ['late arrival', 'late'],
            'Early Leave'   => ['left early', 'early leave'],
            'Overtime'      => ['overtime', 'ot'],
        ];

        $notesCounts = [];

        $notesCounts['No Notes'] = (clone $base)
            ->where(function (Builder $q) {
                $q->whereNull('notes')->orWhere('notes', '=', '');
            })
            ->count();

        foreach ($notePatterns as $label => $keywords) {
            $notesCounts[$label] = (clone $base)
                ->whereNotNull('notes')
                ->where('notes', '!=', '')
                ->where(function (Builder $q) use ($keywords) {
                    foreach ($keywords as $kw) {
                        $q->orWhere('notes', 'like', '%' . $kw . '%');
                    }
                })
                ->count();
        }

        $this->notesTotals = (object) [
            'counts' => $notesCounts
        ];
    }

    public function query()
    {
        $from = $this->from ? trim($this->from) : null;
        $to   = $this->to ? trim($this->to) : null;
        $search = trim($this->search ?? '');

        $baseQuery = AttendanceRegister::query()
            ->with([
                'employee:id,employee_number,name,surname',
                'attendance:id,attendance_number,date,department_id',
                'attendance.department:id,name',
            ])

            // Filter by parent Attendance date
            ->when($from || $to, function (Builder $q) use ($from, $to) {
                $q->whereHas('attendance', function (Builder $a) use ($from, $to) {
                    if ($from && $to) {
                        $a->whereBetween('date', [$from, $to]);
                    } elseif ($from) {
                        $a->where('date', '>=', $from);
                    } elseif ($to) {
                        $a->where('date', '<=', $to);
                    }
                });
            })

            // Department filter is on Attendance
            ->when(filled($this->department_id), function (Builder $q) {
                $q->whereHas('attendance', function (Builder $a) {
                    $a->where('department_id', $this->department_id);
                });
            })

            // Search
            ->when($search !== '', function (Builder $q) use ($search) {
                $like = "%{$search}%";

                $q->where(function (Builder $qq) use ($like) {
                    $qq->where('status', 'like', $like)
                        ->orWhere('shift', 'like', $like)
                        ->orWhere('source', 'like', $like)
                        ->orWhere('notes', 'like', $like)
                        ->orWhereHas('attendance', function (Builder $a) use ($like) {
                            $a->where('attendance_number', 'like', $like)
                            ->orWhere('date', 'like', $like);
                        })
                        ->orWhereHas('attendance.department', function (Builder $d) use ($like) {
                            $d->where('name', 'like', $like);
                        })
                        ->orWhereHas('employee', function (Builder $e) use ($like) {
                            $e->where('employee_number', 'like', $like)
                            ->orWhere(DB::raw("CONCAT(name,' ',surname)"), 'like', $like)
                            ->orWhere('name', 'like', $like)
                            ->orWhere('surname', 'like', $like);
                        });
                });
            });

        return $baseQuery->orderBy($this->attendance_filter, 'desc');
    }

    public function map($row): array
    {
        $employeeName = trim(($row->employee?->name ?? '') . ' ' . ($row->employee?->surname ?? ''));

        return [
            $row->attendance?->attendance_number ?? '-',
            $row->attendance?->date ? Carbon::parse($row->attendance->date)->format('Y-m-d') : '-', // ✅ Attendance date
            $row->employee?->employee_number ?? '-',
            $employeeName ?: '-',
            $this->humanStatus($row->status),
            $row->shift ? ucfirst($row->shift) : '-',
            $row->checkin ? Carbon::parse($row->checkin)->format('H:i') : '-',
            $row->checkout ? Carbon::parse($row->checkout)->format('H:i') : '-',
            $row->source ?? 'manual',
            $row->notes ?? '',
            $row->created_at ? $row->created_at->format('Y-m-d H:i') : '',
        ];
    }

    public function headings(): array
    {
        return [
            'Attendance#',
            'Attendance Date',
            'Employee No',
            'Employee Name',
            'Status',
            'Shift',
            'Check In',
            'Check Out',
            'Source',
            'Notes',
            'Captured At',
        ];
    }

    protected function humanStatus(?string $status): string
    {
        if (!$status) return '-';

        return match ($status) {
            'present'        => 'Present',
            'off'            => 'Off',
            'annual_leave'   => 'Annual Leave',
            'sick_leave'     => 'Sick Leave',
            'unpaid_leave'   => 'Unpaid Leave',
            'training'       => 'Training',
            'public_holiday' => 'Public Holiday',
            'suspension'     => 'Suspension',
            'absent'         => 'Absent',
            default          => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    // ✅ Column D must be fixed so it doesn't expand
    // Also keep Notes fixed so the sheet doesn't blow out
    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 14,
            'C' => 14,
            'D' => 24, // ✅ fixed Employee Name
            'E' => 14,
            'F' => 10,
            'G' => 10,
            'H' => 10,
            'I' => 12,
            'J' => 35,
            'K' => 18,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // Headings row at A8:K8
                $event->sheet->getStyle('A8:K8')->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ],
                ]);

                // Wrap: Employee Name + Status + Notes (keeps widths fixed)
                $highestRow = $sheet->getHighestRow();
                if ($highestRow >= 18) {
                    $sheet->getStyle("D18:D{$highestRow}")->getAlignment()->setWrapText(true);
                    $sheet->getStyle("E18:E{$highestRow}")->getAlignment()->setWrapText(true);
                    $sheet->getStyle("J18:J{$highestRow}")->getAlignment()->setWrapText(true);
                    $sheet->getStyle("D18:J{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                }

                // ---- SUMMARY BLOCK (starts at column C, side-by-side with logo) ----
                // Logo is at A2. Put summary starting C2.
                $row = 2;

                $sheet->setCellValue("C{$row}", 'Attendance Register Summary');
                $sheet->mergeCells("C{$row}:K{$row}");
                $sheet->getStyle("C{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                ]);
                $row++;

                // Date range shown (from Attendance filtering)
                if (filled($this->from) && filled($this->to)) {
                    $fromText = Carbon::parse($this->from)->format('Y-m-d');
                    $toText   = Carbon::parse($this->to)->format('Y-m-d');
                } else {
                    $fromText = now()->startOfMonth()->format('Y-m-d');
                    $toText   = now()->endOfMonth()->format('Y-m-d');
                }

                $deptName = 'All';
                if (filled($this->department_id)) {
                    $deptName = \App\Models\Department::find($this->department_id)?->name ?? 'All';
                }

                $sheet->setCellValue("C{$row}", 'Date Range:');
                $sheet->setCellValue("D{$row}", "{$fromText} to {$toText}");
                $sheet->mergeCells("D{$row}:H{$row}");

                $sheet->setCellValue("I{$row}", 'Department:');
                $sheet->setCellValue("J{$row}", $deptName);
                $sheet->mergeCells("J{$row}:M{$row}");
                $row++;

                $sheet->setCellValue("C{$row}", 'Total Employees Marked:');
                $sheet->setCellValue("D{$row}", (int) ($this->totals->total_marked ?? 0));
                $sheet->mergeCells("D{$row}:H{$row}");
                $row++;

                // ✅ Status counts expanding RIGHT (all of them)
                $sheet->setCellValue("C{$row}", 'Status:');
                $colIndex = 4; // D
                foreach ((array) ($this->totals->status_counts ?? []) as $status => $count) {
                    $sheet->setCellValueByColumnAndRow($colIndex, $row, $this->humanStatus($status) . '=' . (int) $count);
                    $colIndex++;
                }
                $sheet->getStyle("D{$row}:" . $sheet->getCellByColumnAndRow($colIndex - 1, $row)->getCoordinate())
                    ->getAlignment()->setWrapText(true);
                $row++;

                // ✅ Notes counts expanding RIGHT (all of them)
                $sheet->setCellValue("C{$row}", 'Notes:');
                $colIndex = 4; // D
                foreach ((array) ($this->notesTotals->counts ?? []) as $label => $count) {
                    $sheet->setCellValueByColumnAndRow($colIndex, $row, $label . '=' . (int) $count);
                    $colIndex++;
                }
                $sheet->getStyle("D{$row}:" . $sheet->getCellByColumnAndRow($colIndex - 1, $row)->getCoordinate())
                    ->getAlignment()->setWrapText(true);

                // Style the whole summary band C2:K6 (safe width)
                $event->sheet->getStyle("C2:K6")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FCE4D6'],
                    ],
                ]);
            },
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();

        if (isset(Auth::user()->employee->company)) {
            $drawing->setName(Auth::user()->employee->company->name);
            $drawing->setDescription(Auth::user()->employee->company->name . ' Logo');

            if (file_exists(public_path('/images/uploads/' . Auth::user()->employee->company->logo))) {
                $drawing->setPath(public_path('/images/uploads/' . Auth::user()->employee->company->logo));
            } else {
                $drawing->setPath(public_path('/images/uploads/logo.png'));
            }
        } else {
            $drawing->setPath(public_path('/images/uploads/logo.png'));
        }

        $drawing->setHeight(90);
        $drawing->setCoordinates('A2');

        return $drawing;
    }

    public function startCell(): string
    {
        return 'A8';
    }
}