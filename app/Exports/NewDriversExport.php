<?php

namespace App\Exports;

use App\Models\Driver;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
// use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
// use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class NewDriversExport implements FromQuery,
ShouldAutoSize,
WithMapping,
WithHeadings,
WithEvents,
// WithDrawings,
WithCustomStartCell
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        return Driver::query()->with('employee')->where('status',True)->where('archive',False)->sortBy('employee.name')->sortBy('employee.surname');
    }
    public function map($driver): array{

        $departments = $driver->employee->departments;
        if ($departments->count()>0) {
            foreach ($departments as $department) {
                $driver_departments[] = $department->name;
            }
            $departments_string = implode(",",$driver_departments);


        }else {
            $departments_string = "";
        }
        $branch_name =   $driver->employee->branch ? $driver->employee->branch->name : "";
            return   [
                $driver->driver_number,
                $driver->transporter ? $driver->transporter->name : "",
                $driver->employee ? $driver->employee->name : "",
                $driver->employee ? $driver->employee->middle_name : "",
                $driver->employee ? $driver->employee->surname : "",
                $driver->employee ? $driver->employee->dob : "",
                $driver->employee ? $driver->employee->gender : "",
                $driver->employee ? $driver->employee->email : "",
                $driver->employee ? $driver->employee->idnumber : "",
                $driver->employee ? $driver->employee->phonenumber : "",
                $driver->license_number,
                $driver->class,
                $driver->passport_number,
                $driver->experience,
                $branch_name,
                $departments_string,
                $driver->employee ? $driver->employee->pos : "",
                $driver->employee ? $driver->employee->country : "",
                $driver->employee ? $driver->employee->province : "",
                $driver->employee ?  $driver->employee->city : "",
                $driver->employee ?  $driver->employee->suburb : "",
                $driver->employee ? $driver->employee->street_address : "",
                $driver->employee ? $driver->employee->start_date : "",
                $driver->employee ?  $driver->employee->duration : "",
                $driver->employee ?  $driver->employee->expiration : "",
                $driver->employee ?  $driver->employee->leave_days : "",
                $driver->employee ? $driver->employee->next_of_kin : "",
                $driver->employee ? $driver->employee->relationship : "",
                $driver->employee ? $driver->employee->contact : "",
              
                 ];


    }
    public function headings(): array{
            return[
                'Driver#',
                'Transporter',
                'Name',
                'Middle Name',
                'Surname',
                'DOB',
                'Gender',
                'Email',
                'IDNumber',
                'Phonenumber',
                'License#',
                'Class',
                'Passport#',
                'Experience',
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
                $event->sheet->getStyle('A7:AC7')->applyFromArray([
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

    // public function drawings()
    // {
    //     $drawing = new Drawing();
    //     if (isset(Auth::user()->driver->company)) {
    //         $drawing->setName(Auth::user()->driver->company->name);
    //         $drawing->setDescription(Auth::user()->driver->company->name . 'Logo');
    //         $drawing->setPath(public_path('/images/uploads/'.Auth::user()->driver->company->logo));
    //         } 
    //     $drawing->setHeight(90);
    //     $drawing->setCoordinates('A2');

    //     return $drawing;
    // }

    public function startCell(): string{
        return 'A7';
    }
}
