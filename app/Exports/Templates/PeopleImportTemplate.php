<?php

namespace App\Exports\Templates;

use App\Models\Rank;
use App\Models\Role;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Employees / Drivers import templates, generated live so the Roles and Rank
 * dropdowns list this system's own records. Sheet 1 = data (with a "#" example
 * row, skipped on import), "Instructions" = what goes in each column, hidden
 * "Lists" = dropdown source. EmployeesImport / DriversImport read sheet 1 only.
 */
class PeopleImportTemplate
{
    private const VALIDATED_ROWS = 1000;

    public const TYPES = ['employees', 'drivers'];

    public function __construct(private string $type)
    {
    }

    public function download(): StreamedResponse
    {
        $spreadsheet = $this->build();
        $fileName = $this->type === 'drivers' ? 'Drivers Import File.xlsx' : 'Employees Import File.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $fileName, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function build(): Spreadsheet
    {
        $columns = $this->columns();
        $lists = [
            'roles'  => Role::orderBy('name')->pluck('name')->filter()->values()->all(),
            'rank'   => Rank::orderBy('name')->pluck('name')->filter()->values()->all(),
            'gender' => ['Male', 'Female'],
        ];

        $spreadsheet = new Spreadsheet();
        $data = $spreadsheet->getActiveSheet();
        $data->setTitle($this->type === 'drivers' ? 'Drivers' : 'Employees');

        $this->fillDataSheet($data, $columns);
        $this->fillInstructionsSheet($spreadsheet->createSheet(), $columns, $lists);
        $listRanges = $this->fillListsSheet($spreadsheet->createSheet(), $lists);
        $this->addDropdowns($data, $columns, $listRanges);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function fillDataSheet(Worksheet $sheet, array $columns): void
    {
        $i = 1;
        foreach ($columns as $key => $col) {
            $letter = Coordinate::stringFromColumnIndex($i++);
            $sheet->setCellValue("{$letter}1", $key);
            $sheet->getCell("{$letter}2")->setValueExplicit((string) $col['example'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->getStyle("{$letter}2:{$letter}".self::VALIDATED_ROWS)->getNumberFormat()->setFormatCode('@');
            $sheet->getColumnDimension($letter)->setWidth(max(14, min(32, strlen((string) $col['example']) + 4)));

            if ($col['required']) {
                $sheet->getStyle("{$letter}1")->getFont()->getColor()->setRGB('C00000');
            }
        }

        $last = Coordinate::stringFromColumnIndex(count($columns));
        $sheet->getStyle("A1:{$last}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$last}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
        $sheet->getStyle("A2:{$last}2")->getFont()->setItalic(true)->getColor()->setRGB('808080');
        $sheet->freezePane('A2');
    }

    private function fillInstructionsSheet(Worksheet $sheet, array $columns, array $lists): void
    {
        $sheet->setTitle('Instructions');

        $sheet->setCellValue('A1', ucfirst($this->type).' import - how to fill in the "'.($this->type === 'drivers' ? 'Drivers' : 'Employees').'" sheet');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $notes = [
            'One row per person. Only the first sheet is imported; this sheet and the hidden Lists sheet are ignored.',
            'Row 2 is an example: its first cell starts with # so it is skipped on upload. Overwrite it or leave it in.',
            'Name + surname identify a person: re-importing the same name updates that person instead of creating a duplicate.',
            'Columns with a red heading are required. Dates: YYYY-MM-DD (e.g. 2026-01-31).',
            'Roles and Rank have dropdowns listing what exists in the system. Add new roles/ranks in the system first, then download a fresh template.',
        ];
        $row = 2;
        foreach ($notes as $note) {
            $sheet->setCellValue("A{$row}", '• '.$note);
            $sheet->mergeCells("A{$row}:D{$row}");
            $row++;
        }

        $row++;
        $sheet->fromArray(['Column', 'Required?', 'What goes in it', 'Example'], null, "A{$row}");
        $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
        $first = ++$row;

        foreach ($columns as $key => $col) {
            $description = $col['description'];
            if (isset($lists[$key]) && $key !== 'gender') {
                $description .= ' Options: '.(empty($lists[$key]) ? '(none set up yet)' : implode(', ', $lists[$key])).'.';
            }
            $sheet->fromArray([$key, $col['required'] ? 'Yes' : 'No', $description, ltrim((string) $col['example'], '#')], null, "A{$row}");
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(24);
        $sheet->getColumnDimension('B')->setWidth(11);
        $sheet->getColumnDimension('C')->setWidth(90);
        $sheet->getColumnDimension('D')->setWidth(28);
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
                $sheet->setCellValue($letter.($n + 2), $value);
            }
            if (!empty($values)) {
                $ranges[$key] = "'Lists'!\${$letter}\$2:\${$letter}\$".(count($values) + 1);
            }
        }
        $sheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        return $ranges;
    }

    private function addDropdowns(Worksheet $sheet, array $columns, array $listRanges): void
    {
        $i = 1;
        foreach (array_keys($columns) as $key) {
            $letter = Coordinate::stringFromColumnIndex($i++);
            if (!isset($listRanges[$key])) {
                continue;
            }

            $validation = new DataValidation();
            $validation->setType(DataValidation::TYPE_LIST)
                ->setShowDropDown(true)
                ->setFormula1($listRanges[$key])
                ->setAllowBlank(true)
                ->setShowErrorMessage(true)
                // "Information" style warns but still allows typed values, e.g. "Admin, User" for several roles.
                ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                ->setErrorTitle('Not in the list')
                ->setError('This value is not in the system list and will be ignored on import unless it matches an existing name.');

            $sheet->setDataValidation("{$letter}2:{$letter}".self::VALIDATED_ROWS, $validation);
        }
    }

    private function columns(): array
    {
        $person = [
            'name'          => [true,  'First name. Together with surname this identifies the person.', '#Rumbidzai'],
            'middlename'    => [false, 'Middle name(s).', 'Nyasha'],
            'surname'       => [true,  'Last name.', 'Mlambo'],
            'gender'        => [false, 'Male or Female (dropdown). Anything else is left blank.', 'Female'],
            'dob'           => [false, 'Date of birth, YYYY-MM-DD.', '1990-07-21'],
            'email'         => [false, 'Email address. Becomes the login username for new people; login details are emailed here if "Send credential emails" is ticked.', 'rumbi.mlambo@example.com'],
            'phonenumber'   => [false, 'Mobile number. Used as the login username when there is no email.', '+263 77 789 0123'],
            'idnumber'      => [false, 'National ID number.', '63-234567C31'],
        ];

        $driver = [
            'transporter_number' => [false, 'Transporter number (e.g. FPT00015) if the driver belongs to a transporter. Must already exist; left unlinked if not found.', 'FPT00015'],
            'license_number'     => [false, "Driver's licence number. Name + licence number identify the driver record.", '123456AB'],
            'class'              => [false, 'Licence class.', '4'],
            'experience'         => [false, 'Years of driving experience (number).', '5'],
            'passport_number'    => [false, 'Passport number.', 'FN123456'],
        ];

        $address = [
            'country'       => [false, 'Country of residence.', 'Zimbabwe'],
            'city'          => [false, 'City / town.', 'Harare'],
            'suburb'        => [false, 'Suburb.', 'Belgravia'],
            'streetaddress' => [false, 'Street address.', '7 Sam Nujoma St'],
            'nextofkin'     => [false, 'Next of kin full name.', 'Tafadzwa Mlambo'],
            'relationship'  => [false, 'Next of kin relationship (e.g. Wife, Brother).', 'Brother'],
            'contact'       => [false, 'Next of kin phone number.', '+263 77 555 6666'],
        ];

        $reference = [
            'reference'             => [false, 'Reference (previous employer / referee) name.', 'John Banda'],
            'reference_phonenumber' => [false, 'Reference phone number.', '+263 77 333 4444'],
        ];

        $employment = [
            'contract_duration' => [false, 'Contract length in months (number).', '12'],
            'start_date'        => [false, 'Employment start date, YYYY-MM-DD.', '2026-01-01'],
            'expiry_date'       => [false, 'Contract end date, YYYY-MM-DD.', '2026-12-31'],
            'departments'       => [false, $this->type === 'drivers'
                ? 'Department name(s), comma separated. Must already exist; unknown names are ignored.'
                : 'Department name(s), comma separated. Departments that do not exist are created.',
                $this->type === 'drivers' ? 'Operations' : 'Finance, Administration'],
            'post'              => [false, 'Job title. Created if it does not exist.', $this->type === 'drivers' ? 'Driver' : 'Accountant'],
            'roles'             => [false, 'System access role (dropdown). For several, type them comma separated. Blank = "User" for new people; existing people keep their roles. Roles here are added, never removed.', 'User'],
            'rank'              => [false, 'Rank (dropdown). Replaces the person\'s current rank. Blank = "Employee" for new people; existing people keep their rank.', 'Employee'],
        ];

        $all = $this->type === 'drivers'
            ? $person + $driver + $address + $reference + $employment
            : $person + $address + $employment;

        if ($this->type === 'drivers') {
            // transporter_number leads (as in the old template), so it carries the "#" example marker.
            $all = ['transporter_number' => $all['transporter_number']] + $all;
            $all['transporter_number'][2] = '#FPT00015';
            foreach (['name' => 'Tatenda', 'middlename' => 'Blessing', 'surname' => 'Chikwanha', 'gender' => 'Male',
                      'dob' => '1988-03-15', 'email' => 'tatenda.c@example.com', 'phonenumber' => '+263 77 678 9012',
                      'idnumber' => '63-765432B21', 'suburb' => 'Mbare', 'streetaddress' => '15 Ardbennie Rd',
                      'nextofkin' => 'Rudo Chikwanha', 'relationship' => 'Wife', 'contact' => '+263 77 111 2222'] as $key => $example) {
                $all[$key][2] = $example;
            }
        }

        return array_map(fn ($c) => ['required' => $c[0], 'description' => $c[1], 'example' => $c[2]], $all);
    }
}
