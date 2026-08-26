<?php

namespace App\Exports;

use App\Models\Requisition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

class RequisitionExport implements FromQuery,
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
    public $requisition_filter;
    public $search;
    public $department_ids;

   

    public function __construct($from, $to, $requisition_filter, $search)
    {
    
            $this->from = $from;
            $this->to = $to;
            $this->requisition_filter = $requisition_filter;
            $this->search = $search;
    

            $departments = Auth::user()->employee->departments;
            foreach ($departments as $department) {
                $this->department_ids[] = $department->id;
            }
          
           
    }
    public function query()
    { 
        $user = Auth::user();
        $employee = $user->employee;
        $departments = $employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = $user->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = $employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Finance', $department_names) || in_array('Super Admin', $role_names)){
            if (isset($this->from) && isset($this->to)) {
                if (isset($this->search)) {
                    return Requisition::query()->with('employee','department','trip','currency','payments')->whereBetween($this->requisition_filter,[$this->from, $this->to] )
                    ->where('requisition_number','like', '%'.$this->search.'%')
                    ->orWhere('status','like', '%'.$this->search.'%')
                    ->orWhere('date','like', '%'.$this->search.'%')
                    ->orWhereHas('trip', function ($query) {
                        return $query->where('trip_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('employee', function ($query) {
                        return $query->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->search."%");
                    })
                    ->orWhereHas('currency', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy($this->requisition_filter,'desc');
                }else {
                    return requisition::query()->with('employee','department','trip','currency','payments')->whereBetween($this->requisition_filter,[$this->from, $this->to] )->orderBy($this->requisition_filter,'desc');
                }
               
            }
            elseif (isset($this->search)) {
               
                return Requisition::query()->with('employee','department','trip','currency','payments')->whereMonth($this->requisition_filter, date('m'))
                ->whereYear($this->requisition_filter, date('Y'))
                ->where('requisition_number','like', '%'.$this->search.'%')
                ->orWhere('status','like', '%'.$this->search.'%')
                ->orWhere('date','like', '%'.$this->search.'%')
                ->orWhereHas('trip', function ($query) {
                    return $query->where('trip_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('employee', function ($query) {
                    return $query->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->search."%");
                })
                ->orWhereHas('currency', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy($this->requisition_filter,'desc');
            }
            else {
               
                return Requisition::query()->with('employee','department','trip','currency','payments')->whereMonth($this->requisition_filter, date('m'))
                ->whereYear($this->requisition_filter, date('Y'))->orderBy($this->requisition_filter,'desc');
              
            }
           
        }else{

            //not super admin

           

            if (isset($this->from) && isset($this->to)) {
                if (isset($this->search)) {
                    return Requisition::query()->with('employee','department','trip','currency','payments')->whereBetween($this->requisition_filter,[$this->from, $this->to] )
                    ->where('user_id',Auth::user()->id)
                    ->orWhereIn('department_id', $this->department_ids)
                    ->where('requisition_number','like', '%'.$this->search.'%')
                    ->orWhere('status','like', '%'.$this->search.'%')
                    ->orWhere('date','like', '%'.$this->search.'%')
                    ->orWhereHas('trip', function ($query) {
                        return $query->where('trip_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('employee', function ($query) {
                        return $query->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->search."%");
                    })
                    ->orWhereHas('currency', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy($this->requisition_filter,'desc');
                }else {
                    return Requisition::query()->with('employee','department','trip','currency','payments')
                    ->where('user_id',Auth::user()->id)
                    ->orWhereIn('department_id', $this->department_ids)
                    ->whereBetween($this->requisition_filter,[$this->from, $this->to] )->orderBy($this->requisition_filter,'desc');
                }
               
            }
            elseif (isset($this->search)) {
               
                return Requisition::query()->with('employee','department','trip','currency','payments')->whereMonth($this->requisition_filter, date('m'))
                ->where('user_id',Auth::user()->id)
                ->orWhereIn('department_id', $this->department_ids)
                ->whereYear($this->requisition_filter, date('Y'))
                ->where('requisition_number','like', '%'.$this->search.'%')
                ->orWhere('status','like', '%'.$this->search.'%')
                ->orWhere('date','like', '%'.$this->search.'%')
                ->orWhereHas('trip', function ($query) {
                    return $query->where('trip_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('employee', function ($query) {
                    return $query->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->search."%");
                })
                ->orWhereHas('currency', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy($this->requisition_filter,'desc');
            }
            else {
               
                return Requisition::query()->with('employee','department','trip','currency','payments')
                    ->where('user_id',Auth::user()->id)
                    ->orWhereIn('department_id', $this->department_ids)
                    ->whereMonth($this->requisition_filter, date('m'))
                    ->whereYear($this->requisition_filter, date('Y'))->orderBy($this->requisition_filter,'desc');
              
            }
           
        }
       
       
    }


    public function map($requisition): array{

                  
                    $name = $requisition->user ? $requisition->user->name : "";
                    $surname = $requisition->user ? $requisition->user->surname : "";
                    $user = $name." ".$surname;
                    $department = $requisition->employee && $requisition->employee->departments->first() ? $requisition->employee->departments->first()->name : "";
                    $employee_name = $requisition->employee ? $requisition->employee->name : "";
                    $employee_surname = $requisition->employee ? $requisition->employee->surname : "";
                    $employee =  $employee_name." ".  $employee_surname;
                    $symbol = $requisition->currency ? $requisition->currency->symbol : "";
                    $currency = $requisition->currency ? $requisition->currency->name : "";
                    $total =  number_format($requisition->total,2);
                    $paid =  number_format($requisition->paid,2);

                    if ($requisition->requisition_items) {
                        foreach ($requisition->requisition_items as $item) {
                                $name = $item->expense ? $item->expense->name : "";
                                $items[] = $name;
                        }
                        if (isset($items)) {
                            $items_list = implode(', ',$items);
                        }else {
                            $items_list = "";
                        }
                    }else{
                        $items_list = "";
                    }
           

         
               
      
                return   [
                    $requisition->requisition_number ,
                    $user ,
                    $employee ,
                    $department ,
                    $items_list ,
                    $requisition->description,
                    $requisition->date,
                    $currency.' '.$symbol,
                    $total,
                    $requisition->status,
                 
                 
                     ];

    }

    public function headings(): array{
            return[
                'Requisition#',
                'CreatedBy',
                'Requested By',
                'Department',
                'Item(s)',
                'Notes',
                'Date',
                'Currency',
                'Total',
                'Status',
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
