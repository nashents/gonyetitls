<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Booking;
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

class HorseBookingExport implements   FromQuery,
ShouldAutoSize,
WithMapping,
WithHeadings,
WithEvents,
WithDrawings,
WithCustomStartCell
{
    use Exportable;
    /**
    * return \Illuminate\Support\Collection
    */

    public $horse_id;


    public function __construct($horse_id = null) {
        $this->horse_id = $horse_id;
    }

    public function query()
    {
        return Booking::query()->with('ticket','inspection','horse','trailer','vehicle')->where('horse_id',$this->horse_id)
        ->whereYear('created_at', date('Y'))->orderBy('booking_number','desc');
         
     
    }
    public function map($booking): array{


        if (isset($booking->employees) && $booking->employees->count()>0){
            foreach ($booking->employees as $mechanic){
              $mechanics[] =  $mechanic->name." ".$mechanic->surname; 
            }

            if (isset($mechanics)) {
                $assigned_to = implode(',',$mechanics);
            }else {
                $assigned_to = "";
            }

            }elseif(isset($booking->vendor)){

            $assigned_to = ucfirst($booking->vendor->name);

            }else{
            $assigned_to = "";
            }

            if (isset($booking->horse)){
            $booking_for = "Horse | ". ucfirst($booking->horse->horse_make ? $booking->horse->horse_make->name : "") ." ". ucfirst($booking->horse->horse_model ? $booking->horse->horse_model->name : "" ) ." ".  ucfirst($booking->horse ? $booking->horse->registration_number : "") ." ". ucfirst($booking->horse ? "| ".$booking->horse->fleet_number : "");
            }else{
            $booking_for = "";
            }   


            $user = User::find($booking->authorized_by_id);
            if (isset($user)) {
                $authorized_by = $user->name ." ". $user->surname;
            }else{
                $authorized_by = "";
            }
           
            $date = $booking->in_date ." @ ". $booking->in_time;
                                          


            return   [
                $booking->booking_number,
                $booking->user->name ." ". $booking->user->surname,
                $booking->employee ? $booking->employee->name : "",
                $assigned_to,
                $booking_for,
                $booking->service_type ? $booking->service_type->name : "",
                $booking->description,
                $date,
                $booking->authorization,
                $authorized_by,
                $booking->status == 1 ? "Open" : "Closed",
                 ];


    }
    public function headings(): array{
            return[
                'Booking#',
                'CreatedBy ',
                'RequestedBy',
                'AssignedTo',
                'BookingFor',
                'Service Type',
                'Narration',
                'date',
                'Authorization',
                'AuthorizedBy',
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
