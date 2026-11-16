<?php

namespace App\Exports\Templates;

use App\Models\Allowance;
use App\Models\Currency;
use App\Models\Deduction;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
 * Salaries import template, generated live so it carries one column per
 * allowance / deduction set up in this system and lists the employees who
 * still need a salary. Sheet 1 = data (with a "#" example row, skipped on
 * import), sheet 2 "Instructions" = how to fill it in, hidden "Lists" =
 * dropdown source. SalariesImport reads sheet 1 only.
 */
class SalariesImportTemplate
{
    private const VALIDATED_ROWS = 2500;

    public const FREQUENCIES = ['daily', 'weekly', 'fortnightly', 'monthly'];

    /** Worked out by the system on every salary - never typed in. */
    public const AUTO_DEDUCTIONS = ['PAYE', 'AIDS Levy'];

    /** Worked out by the system when rates are configured; a typed amount overrides. */
    public const STATUTORY_DEDUCTIONS = ['NSSA', 'NEC', 'Pension'];

    /**
     * The allowance / deduction columns, keyed the way Laravel Excel slugs
     * their headings ("Allowance: Housing" => "allowance_housing"), so the
     * template and SalariesImport always agree.
     *
     * @return array<string,array{heading:string,column:string,id:int,name:string}>
     */
    public static function itemColumns(bool $activeOnly = true): array
    {
        $columns = [];

        $add = function (string $prefix, string $column, $records) use (&$columns) {
            foreach ($records as $record) {
                $name = trim((string) $record->name);
                $heading = "{$prefix}: {$name}";
                $key = Str::slug($heading, '_');
                if ($name === '' || isset($columns[$key])) {
                    continue;
                }
                $columns[$key] = ['heading' => $heading, 'column' => $column, 'id' => $record->id, 'name' => $name];
            }
        };

        $add('Allowance', 'allowance_id', Allowance::query()
            ->when($activeOnly, fn ($q) => $q->where('status', 1))
            ->orderBy('name')->get(['id', 'name']));

        $add('Deduction', 'deduction_id', Deduction::query()
            ->when($activeOnly, fn ($q) => $q->where('status', 1))
            ->whereNotIn('name', self::AUTO_DEDUCTIONS)
            ->orderBy('name')->get(['id', 'name']));

        return $columns;
    }

    public function download(): StreamedResponse
    {
        $spreadsheet = $this->build();

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'Salaries Import File.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function build(): Spreadsheet
    {
        $currencies = Currency::orderBy('name')->pluck('name')->filter()->values()->all();
        $defaultCurrency = Auth::user()?->employee?->company?->currency?->name ?? ($currencies[0] ?? 'USD');

        $columns = $this->columns($defaultCurrency);
        $lists = [
            'currency'  => $currencies,
            'frequency' => self::FREQUENCIES,
            'paye'      => ['Yes', 'No'],
            'aids_levy' => ['Yes', 'No'],
        ];

        $spreadsheet = new Spreadsheet();
        $data = $spreadsheet->getActiveSheet();
        $data->setTitle('Salaries');

        $this->fillDataSheet($data, $columns);
        $this->fillInstructionsSheet($spreadsheet->createSheet(), $columns, $defaultCurrency);
        $listRanges = $this->fillListsSheet($spreadsheet->createSheet(), $lists);
        $this->addValidation($data, $columns, $listRanges);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function fillDataSheet(Worksheet $sheet, array $columns): void
    {
        $i = 1;
        foreach ($columns as $col) {
            $letter = Coordinate::stringFromColumnIndex($i++);
            $range = "{$letter}2:{$letter}".self::VALIDATED_ROWS;

            $sheet->setCellValue("{$letter}1", $col['heading']);
            if ($col['numeric']) {
                $sheet->getStyle($range)->getNumberFormat()->setFormatCode('0.00');
                if ($col['example'] !== '') {
                    $sheet->setCellValue("{$letter}2", (float) $col['example']);
                }
            } else {
                $sheet->getStyle($range)->getNumberFormat()->setFormatCode('@');
                $sheet->getCell("{$letter}2")->setValueExplicit((string) $col['example'], DataType::TYPE_STRING);
            }
            $sheet->getColumnDimension($letter)->setWidth(max(14, min(34, strlen($col['heading']) + 4)));

            if ($col['required']) {
                $sheet->getStyle("{$letter}1")->getFont()->getColor()->setRGB('C00000');
            }
        }

        // Employees still without a salary, ready to fill in.
        $row = 3;
        foreach ($this->employeesWithoutSalary() as $employee) {
            $sheet->getCell("A{$row}")->setValueExplicit((string) $employee->employee_number, DataType::TYPE_STRING);
            $sheet->getCell("B{$row}")->setValueExplicit(trim((string) $employee->name), DataType::TYPE_STRING);
            $sheet->getCell("C{$row}")->setValueExplicit(trim((string) $employee->surname), DataType::TYPE_STRING);
            $row++;
        }

        $last = Coordinate::stringFromColumnIndex(count($columns));
        $sheet->getStyle("A1:{$last}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$last}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
        $sheet->getStyle("A1:{$last}1")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A2:{$last}2")->getFont()->setItalic(true)->getColor()->setRGB('808080');
        // Keep the heading row and the employee columns in view while scrolling.
        $sheet->freezePane('D2');
    }

    private function fillInstructionsSheet(Worksheet $sheet, array $columns, string $defaultCurrency): void
    {
        $sheet->setTitle('Instructions');

        $row = 1;
        $sheet->setCellValue("A{$row}", 'Salaries import - how to fill in the "Salaries" sheet');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
        $row++;

        $section = function (string $title, array $notes) use ($sheet, &$row) {
            $row++;
            $sheet->setCellValue("A{$row}", $title);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
            $row++;
            foreach ($notes as $note) {
                $sheet->setCellValue("A{$row}", '• '.$note);
                $sheet->mergeCells("A{$row}:D{$row}");
                $sheet->getStyle("A{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                // Merged cells do not auto-size: allow one line per ~130 characters.
                $sheet->getRowDimension($row)->setRowHeight(15 * max(1, (int) ceil(strlen($note) / 130)));
                $row++;
            }
        };

        $section('The basics', [
            'One row per employee. Only the first sheet ("Salaries") is imported; this sheet is ignored.',
            'Row 2 is an example: its first cell starts with # so it is skipped on upload. Overwrite it or leave it in.',
            'Employees who do not have a salary yet are already listed for you. Fill in the ones you want; a listed employee with nothing filled in is simply ignored.',
            'Each employee is found by employee_number. If you leave it blank, name + surname are used instead (they must match the system exactly and be unique).',
            'Columns with a red heading are required. Amounts are plain numbers (e.g. 1250 or 1250.50) with no currency symbol.',
            'Every amount on a row - basic, allowances and deductions - is in that row\'s salary currency.',
        ]);

        $section('Allowances and deductions', [
            'There is one "Allowance: ..." column for every active allowance and one "Deduction: ..." column for every active deduction in the system.',
            'Type the amount the employee gets per pay period under each one that applies. Leave the others blank.',
            'Need an allowance or deduction that has no column? Add it in the system first, then download a fresh template. Do not rename or invent columns - a column the system does not recognise is not imported and you are warned about it.',
            'PAYE and AIDS Levy have no columns: answer Yes / No in the paye and aids_levy columns and the system works them out from the tax tables.',
            'NSSA, NEC and Pension are worked out automatically where rates are set up for the employee. If this template has a column for one of them, only type an amount there to override the automatic figure.',
            'Loans and recoveries are not imported. Add them on the salary afterwards (Salaries > Edit).',
        ]);

        $section('How the salary is calculated', [
            'Gross = basic + all allowances.',
            'PAYE (if Yes) = tax table for the salary currency and frequency, applied to gross less non-taxable allowances. AIDS Levy (if Yes) = 3% of PAYE.',
            'Net = gross - deductions (your deduction columns + PAYE + AIDS Levy + NSSA / NEC / Pension + any loans already on the salary).',
            'This is the same calculation as Salaries > Add Salary, so an imported salary and a hand-captured one give the same result.',
        ]);

        $section('Uploading again / changing salaries', [
            'An employee who already has a salary is updated, never duplicated. To change existing salaries, add a row with the employee_number.',
            'A blank cell means "leave as it is". It never clears anything.',
            'To remove an allowance or deduction from an existing salary, type 0 in its column.',
            'A row with a problem (unknown employee, invalid amount, unknown currency ...) is skipped and listed on screen after the upload; all other rows are still imported. Fix the listed rows and upload the file again.',
        ]);

        $row++;
        $sheet->fromArray(['Column', 'Required?', 'What goes in it', 'Example'], null, "A{$row}");
        $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
        $first = ++$row;

        $itemsDescribed = [];
        foreach ($columns as $col) {
            // One line for all allowance columns and one for all deduction columns.
            if ($col['group']) {
                if (isset($itemsDescribed[$col['group']])) {
                    continue;
                }
                $itemsDescribed[$col['group']] = true;
                $names = array_column(array_filter($columns, fn ($c) => $c['group'] === $col['group']), 'name');
                $sheet->fromArray([
                    "{$col['group']}: ... (".count($names).' column(s))',
                    'No',
                    "Amount per pay period, in the salary currency. Blank = does not apply (or, for an existing salary, unchanged). 0 = remove it from an existing salary. Columns in this template: ".implode(', ', $names).'.',
                    '150',
                ], null, "A{$row}");
            } else {
                $sheet->fromArray([$col['heading'], $col['required'] ? 'Yes' : 'No', $col['description'], ltrim((string) $col['example'], '#')], null, "A{$row}");
                $sheet->getCell("D{$row}")->setValueExplicit(ltrim((string) $col['example'], '#'), DataType::TYPE_STRING);
            }
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(26);
        $sheet->getColumnDimension('B')->setWidth(11);
        $sheet->getColumnDimension('C')->setWidth(100);
        $sheet->getColumnDimension('D')->setWidth(22);
        $sheet->getStyle("A{$first}:D{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle("A{$first}:A{$row}")->getFont()->setBold(true);
    }

    /** @return array<string,string> column key => absolute range on the Lists sheet */
    private function fillListsSheet(Worksheet $sheet, array $lists): array
    {
        $sheet->setTitle('Lists');
        $ranges = [];
        $i = 1;
        foreach ($lists as $key => $values) {
            $letter = Coordinate::stringFromColumnIndex($i++);
            $sheet->setCellValue("{$letter}1", $key);
            foreach (array_values($values) as $n => $value) {
                $sheet->getCell($letter.($n + 2))->setValueExplicit((string) $value, DataType::TYPE_STRING);
            }
            if (!empty($values)) {
                $ranges[$key] = "'Lists'!\${$letter}\$2:\${$letter}\$".(count($values) + 1);
            }
        }
        $sheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        return $ranges;
    }

    private function addValidation(Worksheet $sheet, array $columns, array $listRanges): void
    {
        $i = 1;
        foreach ($columns as $key => $col) {
            $letter = Coordinate::stringFromColumnIndex($i++);
            $range = "{$letter}2:{$letter}".self::VALIDATED_ROWS;

            if (isset($listRanges[$key])) {
                $validation = new DataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setShowDropDown(true)
                    ->setFormula1($listRanges[$key])
                    ->setAllowBlank(true)
                    ->setShowErrorMessage(true)
                    ->setErrorStyle(DataValidation::STYLE_STOP)
                    ->setErrorTitle('Not in the list')
                    ->setError('Pick a value from the dropdown list.');
                $sheet->setDataValidation($range, $validation);
            } elseif ($col['numeric']) {
                $validation = new DataValidation();
                $validation->setType(DataValidation::TYPE_DECIMAL)
                    ->setOperator(DataValidation::OPERATOR_GREATERTHANOREQUAL)
                    ->setFormula1('0')
                    ->setAllowBlank(true)
                    ->setShowErrorMessage(true)
                    ->setErrorStyle(DataValidation::STYLE_STOP)
                    ->setErrorTitle('Amount')
                    ->setError('Enter a number of 0 or more, without a currency symbol.');
                $sheet->setDataValidation($range, $validation);
            }
        }
    }

    /** Same people the Add Salary form offers, minus those who already have a salary. */
    private function employeesWithoutSalary()
    {
        return Employee::query()
            ->activeStaff()
            ->whereHas('user', fn ($q) => $q->where('category', '!=', 'admin'))
            ->whereDoesntHave('salaries')
            ->orderBy('name')
            ->orderBy('surname')
            ->limit(self::VALIDATED_ROWS - 2)
            ->get(['id', 'employee_number', 'name', 'surname']);
    }

    private function columns(string $defaultCurrency): array
    {
        $fixed = [
            'employee_number' => [false, 'The employee\'s number in the system (Employees list). This is what identifies the employee. May be left blank if name + surname are filled in and unique.', '#EMP00001'],
            'name'            => [false, 'First name. Only used to find the employee when employee_number is blank; otherwise it is just there so you can see who the row is for.', 'Rumbidzai'],
            'surname'         => [false, 'Surname. Used together with name when employee_number is blank.', 'Mlambo'],
            'currency'        => [false, "Salary currency (dropdown). Blank = {$defaultCurrency} for a new salary; an existing salary keeps its currency.", $defaultCurrency],
            'frequency'       => [false, 'How often the employee is paid (dropdown): daily, weekly, fortnightly or monthly. Blank = monthly for a new salary.', 'monthly'],
            'basic'           => [true,  'Basic salary per pay period. Required for a new salary; blank on an existing salary keeps the current basic.', '1200', true],
            'paye'            => [false, 'Yes = deduct PAYE (calculated by the system from the tax tables). Blank = No for a new salary.', 'Yes'],
            'aids_levy'       => [false, 'Yes = deduct AIDS Levy (3% of PAYE). Only has an effect when paye is Yes. Blank = No for a new salary.', 'Yes'],
        ];

        $columns = [];
        foreach ($fixed as $key => $c) {
            $columns[$key] = [
                'heading' => $key, 'required' => $c[0], 'description' => $c[1], 'example' => $c[2],
                'numeric' => $c[3] ?? false, 'group' => null, 'name' => $key,
            ];
        }

        $exampled = [];
        foreach (self::itemColumns() as $key => $item) {
            $group = $item['column'] === 'allowance_id' ? 'Allowance' : 'Deduction';
            $statutory = in_array(strtolower($item['name']), array_map('strtolower', self::STATUTORY_DEDUCTIONS), true);
            // The example row shows an amount under the first allowance and the first ordinary deduction.
            $example = '';
            if (!$statutory && !isset($exampled[$group])) {
                $exampled[$group] = true;
                $example = $group === 'Allowance' ? '150' : '50';
            }
            $columns[$key] = [
                'heading' => $item['heading'], 'required' => false, 'description' => '', 'example' => $example,
                'numeric' => true, 'group' => $group, 'name' => $item['name'],
            ];
        }

        return $columns;
    }
}
