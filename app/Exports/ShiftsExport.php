<?php

namespace App\Exports;

use App\Models\Shift;
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

class ShiftsExport implements  FromQuery,
ShouldAutoSize,
WithMapping,
WithHeadings,
WithEvents,
WithDrawings,
WithCustomStartCell
{
    use Exportable;
    public $commission;
    /**
    * @return \Illuminate\Support\Collection
    */
    public $from;
    public $to;
    public $shift_filter;
    public $search;
   

    public function __construct($from, $to, $shift_filter, $search)
    {
            $this->from = $from;
            $this->to = $to;
            $this->shift_filter = $shift_filter;
            $this->search = $search; 
    }
    public function query()
    { 
        if (isset($this->from) && isset($this->to)) {
            if (isset($this->search)) {
                return Shift::query()->with(['customer:id,name','driver','horse','vehicle','cargo','transporter','fuel'])->whereBetween($this->shift_filter,[$this->from, $this->to] )
                            ->where('shift_number','like', '%'.$this->search.'%')
                            ->orWhere('date','like', '%'.$this->search.'%')
                            ->orWhere('type','like', '%'.$this->search.'%')
                            ->orWhere('for','like', '%'.$this->search.'%')
                            ->orWhereHas('customer', function ($query) {
                                return $query->where('name', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('horse', function ($query) {
                                return $query->where('registration_number', 'like', '%'.$this->search.'%')
                                            ->orWhere('fleet_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('vehicle', function ($query) {
                                return $query->where('registration_number', 'like', '%'.$this->search.'%')
                                            ->orWhere('fleet_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('cargo', function ($query) {
                                return $query->where('name', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('transporter', function ($query) {
                                return $query->where('name', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('driver.employee', function ($query) {
                                return $query->where('name', 'like', '%'.$this->search.'%')
                                            ->orWhere('surname', 'like', '%'.$this->search.'%');
                            })
                            ->orderBy($this->shift_filter,'desc');
            }else {
               return Shift::query()->with(['customer:id,name','driver','horse','vehicle','cargo','transporter','fuel'])
                            ->whereBetween($this->shift_filter,[$this->from, $this->to] )->orderBy($this->shift_filter,'desc');
            }
           
        }elseif ($this->search) {
            return Shift::query()->with(['customer:id,name','driver','horse','vehicle','cargo','transporter','fuel'])
                        ->whereMonth($this->shift_filter, date('m'))
                        ->whereYear($this->shift_filter, date('Y'))
                        ->where('shift_number','like', '%'.$this->search.'%')
                        ->where('shift_number','like', '%'.$this->search.'%')
                        ->orWhere('date','like', '%'.$this->search.'%')
                        ->orWhere('type','like', '%'.$this->search.'%')
                        ->orWhere('for','like', '%'.$this->search.'%')
                        ->orWhereHas('customer', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%')
                                        ->orWhere('fleet_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('vehicle', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%')
                                        ->orWhere('fleet_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('cargo', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('transporter', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('driver.employee', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%')
                                        ->orWhere('surname', 'like', '%'.$this->search.'%');
                        })
                        ->orderBy($this->shift_filter,'desc');
        }
        else {
           return Shift::query()->with(['customer:id,name','driver','horse','vehicle','cargo','transporter','fuel'])
                        ->whereMonth($this->shift_filter, date('m'))
                        ->whereYear($this->shift_filter, date('Y'))
                        ->orderBy($this->shift_filter,'desc');
          
        }
       
       
    }


    public function map($shift): array{

                $equipment = "";
                if ($shift->equipment == "Horse") {
                    $reg_number = $shift->horse->registration_number;
                    $fleet_number = $shift->horse->fleet_number;
                    $equipment = $reg_number."(".$fleet_number.")";
                }elseif ($shift->equipment == "Vehicle") {
                    $reg_number = $shift->vehicle->registration_number;
                    $fleet_number = $shift->vehicle->fleet_number;
                    $equipment = $reg_number."(".$fleet_number.")";
                }

                $driver = $shift->driver->employee->name ." ". $shift->driver->employee->surname;

               
      
                return   [
                    $shift->shift_number ,
                    $shift->type ,
                    $shift->for ,
                    $shift->date ,
                    $shift->shift_start_time ,
                    $shift->shift_end_time ,
                    $shift->customer ? $shift->customer->name : "",
                    $shift->cargo ? $shift->cargo->name : "",
                    $equipment,
                    $driver
                     ];

    }

    public function headings(): array{
            return[
                'Shift#',
                'Type',
                'For',
                'Date',
                'Start Time',
                'Close Time',
                'Customer',
                'Cargo',
                'Equipment',
                'Driver',
            ];
    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:J7')->applyFromArray([
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
          if (file_exists(public_path('/images/uploads/'.Auth::user()->employee->company->logo))){
            $drawing->setPath(public_path('/images/uploads/'.Auth::user()->employee->company->logo));
        }else{
            $drawing->setPath(public_path('/images/uploads/logo.png'));
        }
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
