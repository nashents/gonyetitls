<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\AttendanceRegister;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AttendanceRegisterExport implements
    FromCollection,
    ShouldAutoSize,
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

    // Row numbers (absolute sheet rows) of the department banner/subtotal rows,
    // populated by collection() so registerEvents() can style/merge them.
    protected $departmentHeaderRows = [];
    protected $departmentSubtotalRows = [];

    public $auth_status;

    public function __construct($from = null, $to = null, $department_id = null, $search = null, $attendance_filter = 'created_at', $auth_status = null)
    {
        $this->auth_status = $auth_status;
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
                'employee.default_departments:departments.id,departments.name',
                'attendance:id,attendance_number,date,department_id',
                'attendance.department:id,name',
            ])

            // One row per employee per day: when an employee was marked on
            // several department registers for the same date, keep only the
            // one marked under their DEFAULT department.
            ->where(function (Builder $q) {
                $q->whereExists(function ($e) {
                    $e->select(DB::raw(1))
                        ->from('department_employee as de')
                        ->join('attendances as a_def', 'a_def.department_id', '=', 'de.department_id')
                        ->whereColumn('a_def.id', 'attendance_registers.attendance_id')
                        ->whereColumn('de.employee_id', 'attendance_registers.employee_id')
                        ->where('de.is_default', true);
                })->orWhereNotExists(function ($e) {
                    $e->select(DB::raw(1))
                        ->from('attendance_registers as ar2')
                        ->join('attendances as a2', 'a2.id', '=', 'ar2.attendance_id')
                        ->join('attendances as a_self', 'a_self.date', '=', 'a2.date')
                        ->whereColumn('a_self.id', 'attendance_registers.attendance_id')
                        ->whereColumn('ar2.employee_id', 'attendance_registers.employee_id')
                        ->whereColumn('ar2.id', '!=', 'attendance_registers.id')
                        ->whereNull('ar2.deleted_at')
                        ->whereNull('a2.deleted_at');
                });
            })

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

            // Auth status of the parent attendance register (index table filter)
            ->when(filled($this->auth_status), function (Builder $q) {
                $q->whereHas('attendance', fn (Builder $a) => \App\Http\Livewire\Concerns\FiltersAuthStatus::filterByAuthStatus($a, $this->auth_status));
            })

            // Department filter is on the employee's DEFAULT department
            ->when(filled($this->department_id), function (Builder $q) {
                $q->whereHas('employee', fn (Builder $e) => $e->inDefaultDepartment($this->department_id));
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

    public function collection()
    {
        $records = $this->query()->get();

        $grouped = $records
            ->groupBy(fn ($row) => $this->departmentName($row))
            ->sortKeys();

        $rows = collect();
        $currentRow = 9; // data starts immediately after the heading row (startCell 'A8')

        foreach ($grouped as $departmentName => $departmentRows) {
            $rows->push($this->bannerRow("{$departmentName} — {$departmentRows->count()} record" . ($departmentRows->count() === 1 ? '' : 's')));
            $this->departmentHeaderRows[] = $currentRow++;

            foreach ($departmentRows as $row) {
                $rows->push($this->mapRow($row));
                $currentRow++;
            }

            $rows->push($this->bannerRow("Subtotal — {$departmentName}: {$departmentRows->count()} record" . ($departmentRows->count() === 1 ? '' : 's')));
            $this->departmentSubtotalRows[] = $currentRow++;
        }

        return $rows;
    }

    protected function bannerRow(string $label): array
    {
        return array_merge([$label], array_fill(0, 11, ''));
    }

    protected function mapRow($row): array
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
            $this->departmentName($row, '-'),
        ];
    }

    /** The employee's default department (never a secondary one). */
    protected function departmentName($row, string $none = 'Unassigned'): string
    {
        return $row->employee?->default_departments->first()?->name ?: $none;
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
            'Department',
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
            'L' => 16,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // Headings row at A8:L8
                $event->sheet->getStyle('A8:L8')->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ],
                ]);

                // Department banner rows
                foreach ($this->departmentHeaderRows as $row) {
                    $sheet->mergeCells("A{$row}:L{$row}");
                    $event->sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FF1F4E78']],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'D9E1F2'],
                        ],
                    ]);
                }

                // Department subtotal rows
                foreach ($this->departmentSubtotalRows as $row) {
                    $sheet->mergeCells("A{$row}:L{$row}");
                    $event->sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                        'font' => ['italic' => true],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                        'borders' => [
                            'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        ],
                    ]);
                }

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