<?php

namespace App\Exports;

use App\Models\Employee;
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

class EmployeesExport implements  FromQuery,
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
    public function query()
    {
        return Employee::query()->doesntHave('driver')->where('archive', 0)->orderBy('name','asc')->orderBy('surname','asc');
    }
    public function map($employee): array{

        $departments = $employee->departments;
        if ($departments->count()>0) {
            foreach ($departments as $department) {
                $employee_departments[] = $department->name;
            }
            $departments_string = implode(",",$employee_departments);


        }else {
            $departments_string = "";
        }
        $branch_name =   $employee->branch ? $employee->branch->name : "";
            return   [
                $employee->employee_number,
                $employee->name,
                $employee->middle_name,
                $employee->surname,
                $employee->dob,
                $employee->gender,
                $employee->email,
                $employee->idnumber,
                $employee->phonenumber,
                $branch_name,
                $departments_string,
                $employee->post,
                $employee->country,
                $employee->province,
                $employee->city,
                $employee->suburb,
                $employee->street_address,
                $employee->start_date,
                $employee->duration,
                $employee->expiration,
                $employee->leave_days,
                $employee->next_of_kin,
                $employee->relationship,
                $employee->contact,
              
                 ];


    }
    public function headings(): array{
            return[
                'Employee#',
                'Name',
                'Middle Name',
                'Surname',
                'DOB',
                'Gender',
                'Email',
                'IDNumber',
                'Phonenumber',
                'Branch',
                'Departments',
                'Post',
                'Country',
                'Province',
                'City',
                'Suburb',
                'Street Address',
                'Start Date',
                'Duration',
                'Expiration',
                'Leave Days',
                'Next Of Kin',
                'Relationship',
                'Contact',
               
               
              

            ];


    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:X7')->applyFromArray([
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
