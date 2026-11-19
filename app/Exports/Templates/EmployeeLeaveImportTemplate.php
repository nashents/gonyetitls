<?php

namespace App\Exports\Templates;

use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\LeaveType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Employee leave days import template, generated live: one row per active
 * employee x active leave type, pre-filled with the CURRENT accrual rate /
 * available days / maximum days, so HR only edits numbers - no typing of
 * employee numbers or leave type names. Sheet 1 = data (with a "#" example
 * row, skipped on import), sheet 2 = Instructions, hidden "Lists" = leave
 * type dropdown. Headings are the same as the old static file, so
 * EmployeesLeaveImport reads both.
 */
class EmployeeLeaveImportTemplate
{
    /** Same headings EmployeesLeaveImport expects. */
    private const COLUMNS = [
        // heading => [width, numeric, description, example]
        'employee_number'      => [16, false, 'The employee\'s number in the system. This is what identifies the employee - do not change it. If blank, fullname is used instead.', '#EMP00001'],
        'fullname'             => [28, false, 'Name and surname. Only used to find the employee when employee_number is blank; otherwise it is just there so you can see who the row is for.', 'Rumbidzai Mlambo'],
        'leave_type'           => [22, false, 'The leave type (dropdown). Pre-filled - do not change it unless you add a new row.', 'Annual'],
        'accrual_rate'         => [14, true,  'Days earned per month (accruing leave types only).', '2.5'],
        'available_leave_days' => [20, true,  'The employee\'s current leave balance for this leave type.', '12'],
        'maximum_leave_days'   => [20, true,  'The most days the employee can hold / take for this leave type.', '30'],
    ];

    private string $staffType;

    public function __construct(string $staffType = 'all')
    {
        $this->staffType = $staffType;
    }

    public function download(): StreamedResponse
    {
        $spreadsheet = $this->build();

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'Leave Days Import File.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function build(): Spreadsheet
    {
        $leaveTypes = LeaveType::where('active', true)->orderBy('name')->get(['id', 'name']);

        $spreadsheet = new Spreadsheet();
        $data = $spreadsheet->getActiveSheet();
        $data->setTitle('Leave Days');

        $lastRow = $this->fillDataSheet($data, $leaveTypes);
        $this->fillInstructionsSheet($spreadsheet->createSheet(), $leaveTypes);
        $listRange = $this->fillListsSheet($spreadsheet->createSheet(), $leaveTypes->pluck('name')->all());
        $this->addValidation($data, $listRange, $lastRow + 500);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /** @return int last filled row */
    private function fillDataSheet(Worksheet $sheet, $leaveTypes): int
    {
        $i = 1;
        foreach (self::COLUMNS as $heading => [$width, $numeric, , $example]) {
            $letter = Coordinate::stringFromColumnIndex($i++);
            $sheet->setCellValue("{$letter}1", $heading);
            $sheet->getColumnDimension($letter)->setWidth($width);
            if ($numeric) {
                $sheet->setCellValue("{$letter}2", (float) $example);
            } else {
                $sheet->getCell("{$letter}2")->setValueExplicit($example, DataType::TYPE_STRING);
            }
        }

        $employees = Employee::query()
            ->activeStaff()
            ->ofStaffType($this->staffType)
            ->orderBy('name')->orderBy('surname')->orderBy('id')
            ->get(['id', 'employee_number', 'name', 'surname', 'accrual_rate', 'leave_days', 'maximum_leave_days']);

        $records = EmployeeLeave::whereIn('employee_id', $employees->pluck('id'))
            ->whereIn('leave_type_id', $leaveTypes->pluck('id'))
            ->get(['employee_id', 'leave_type_id', 'acrual_rate', 'available_leave_days', 'maximum_leave_days'])
            ->keyBy(fn ($r) => $r->employee_id.'-'.$r->leave_type_id);

        $row = 3;
        foreach ($employees as $employee) {
            $fullname = trim($employee->name.' '.$employee->surname);
            foreach ($leaveTypes as $leaveType) {
                $record = $records->get($employee->id.'-'.$leaveType->id);

                $values = $record
                    ? [$record->acrual_rate, $record->available_leave_days, $record->maximum_leave_days]
                    : [null, null, null];

                // Annual leave was historically kept on the employee row itself.
                if (!$record && strtolower(trim($leaveType->name)) === 'annual') {
                    $values = [$employee->accrual_rate, $employee->leave_days, $employee->maximum_leave_days];
                }

                $sheet->getCell("A{$row}")->setValueExplicit((string) $employee->employee_number, DataType::TYPE_STRING);
                $sheet->getCell("B{$row}")->setValueExplicit($fullname, DataType::TYPE_STRING);
                $sheet->getCell("C{$row}")->setValueExplicit((string) $leaveType->name, DataType::TYPE_STRING);
                foreach (['D', 'E', 'F'] as $n => $col) {
                    if ($values[$n] !== null && $values[$n] !== '' && is_numeric($values[$n])) {
                        $sheet->setCellValue("{$col}{$row}", (float) $values[$n]);
                    }
                }
                $row++;
            }
        }
        $lastRow = $row - 1;

        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
        $sheet->getStyle('A2:F2')->getFont()->setItalic(true)->getColor()->setRGB('808080');
        // Identity columns are reference only - grey them so the editable numbers stand out.
        if ($lastRow >= 3) {
            $sheet->getStyle("A3:C{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
        }
        $sheet->getStyle('A:C')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('D:F')->getNumberFormat()->setFormatCode('0.00');
        $sheet->setAutoFilter("A1:F{$lastRow}");
        $sheet->freezePane('D2');

        return $lastRow;
    }

    private function fillInstructionsSheet(Worksheet $sheet, $leaveTypes): void
    {
        $sheet->setTitle('Instructions');

        $row = 1;
        $sheet->setCellValue("A{$row}", 'Leave days import - how to fill in the "Leave Days" sheet');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
        $row++;

        $notes = [
            'Every active employee is already listed once per active leave type ('.$leaveTypes->count().' types: '.$leaveTypes->pluck('name')->implode(', ').'), with their CURRENT figures filled in.',
            'Just change the numbers you want to update (accrual_rate, available_leave_days, maximum_leave_days) and upload the file. You do not need to type employee numbers or leave type names.',
            'Use the filter arrows on the heading row to show one leave type or one employee at a time.',
            'Rows you do not change are re-saved with the same figures, so leaving them in is harmless. You may also delete rows you are not updating.',
            'A blank cell means "leave as it is" - it never clears a figure. A row with all three figures blank is skipped.',
            'Row 2 is an example: its first cell starts with # so it is skipped on upload.',
            'Only the first sheet is imported. Numbers only (e.g. 2.5), no text.',
            'Updating Annual leave also updates the employee\'s leave days shown on their profile, exactly like editing it on screen.',
            'Rows with an unknown employee or leave type are skipped and listed on screen after the upload; all other rows are still imported.',
            'Need a leave type that is not listed? Activate it under Leave Types, then download a fresh template.',
        ];

        foreach ($notes as $note) {
            $row++;
            $sheet->setCellValue("A{$row}", '• '.$note);
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->getStyle("A{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getRowDimension($row)->setRowHeight(15 * max(1, (int) ceil(strlen($note) / 130)));
        }

        $row += 2;
        $sheet->fromArray(['Column', 'What goes in it', '', 'Example'], null, "A{$row}");
        $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
        $first = ++$row;
        foreach (self::COLUMNS as $heading => [, , $description, $example]) {
            $sheet->setCellValue("A{$row}", $heading);
            $sheet->setCellValue("B{$row}", $description);
            $sheet->mergeCells("B{$row}:C{$row}");
            $sheet->getCell("D{$row}")->setValueExplicit(ltrim($example, '#'), DataType::TYPE_STRING);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(24);
        $sheet->getColumnDimension('B')->setWidth(70);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getStyle("A{$first}:D{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle("A{$first}:A{$row}")->getFont()->setBold(true);
    }

    private function fillListsSheet(Worksheet $sheet, array $names): ?string
    {
        $sheet->setTitle('Lists');
        $sheet->setCellValue('A1', 'leave_type');
        foreach (array_values($names) as $n => $name) {
            $sheet->getCell('A'.($n + 2))->setValueExplicit((string) $name, DataType::TYPE_STRING);
        }
        $sheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        return empty($names) ? null : "'Lists'!\$A\$2:\$A\$".(count($names) + 1);
    }

    private function addValidation(Worksheet $sheet, ?string $listRange, int $lastValidatedRow): void
    {
        if ($listRange) {
            $validation = new DataValidation();
            $validation->setType(DataValidation::TYPE_LIST)
                ->setShowDropDown(true)
                ->setFormula1($listRange)
                ->setAllowBlank(true)
                ->setShowErrorMessage(true)
                ->setErrorStyle(DataValidation::STYLE_STOP)
                ->setErrorTitle('Not in the list')
                ->setError('Pick a leave type from the dropdown list.');
            $sheet->setDataValidation("C2:C{$lastValidatedRow}", $validation);
        }

        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_DECIMAL)
            ->setOperator(DataValidation::OPERATOR_GREATERTHANOREQUAL)
            ->setFormula1('0')
            ->setAllowBlank(true)
            ->setShowErrorMessage(true)
            ->setErrorStyle(DataValidation::STYLE_STOP)
            ->setErrorTitle('Number')
            ->setError('Enter a number of 0 or more.');
        $sheet->setDataValidation("D2:F{$lastValidatedRow}", $validation);
    }
}
