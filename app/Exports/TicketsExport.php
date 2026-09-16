<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Ticket;
use App\Support\FleetIdentifier;
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

class TicketsExport implements  FromQuery,
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

    public $search;
    public $from;
    public $to;
    public $ticket_status;
    public $filter;
    public $selectedHorse;
    public $selectedTrailer;
    public $selectedAsset;
    public $selectedVehicle;


    public function __construct($ticket_status = null, $filter = null, $search = null, $from = null, $to = null ) {
        $this->ticket_status = $ticket_status;
        $this->search = $search;
        $this->from = $from;
        $this->to = $to;
        $this->filter = $filter;
    }

    public function query()
    {
        $query = Ticket::query()
            ->with(['booking', 'inspection', 'horse', 'trailer', 'vehicle', 'booking.employee', 'service_type']);

        // ✅ Status filter
        if ($this->ticket_status !== 'all') {
            $query->where('status', $this->ticket_status);
        }

        // ✅ Extra filter (horse, trailer, asset, vehicle)
        if (!empty($this->filter)) {
            switch ($this->filter) {
                case "horse":
                    $query->where('horse_id', $this->selectedHorse);
                    break;
                case "trailer":
                    $query->where('trailer_id', $this->selectedTrailer);
                    break;
                case "asset":
                    $query->where('asset_id', $this->selectedAsset);
                    break;
                case "vehicle":
                    $query->where('vehicle_id', $this->selectedVehicle);
                    break;
            }
        }

        // ✅ Date filter
        if (!empty($this->from) && !empty($this->to)) {
            $query->whereBetween('created_at', [$this->from, $this->to]);
        } else {
            $query->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'));
        }

        // ✅ Search filter
        if (($search = trim((string) $this->search)) !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                ->orWhere('in_date', 'like', "%{$search}%")
                ->orWhere('in_time', 'like', "%{$search}%")
                ->orWhere('out_date', 'like', "%{$search}%")
                ->orWhere('out_time', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('station', 'like', "%{$search}%")
                ->orWhereHas('inspection', function ($q2) use ($search) {
                    $q2->where('inspection_number', 'like', "%{$search}%");
                })
                ->orWhereHas('service_type', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('booking', function ($q2) use ($search) {
                    $q2->where('booking_number', 'like', "%{$search}%")
                        ->orWhereHas('employee', function ($q3) use ($search) {
                            $q3->where('name', 'like', "%{$search}%");
                        });
                });
            });
        }

        // ✅ Order (Excel exports usually don’t paginate)
        return $query->orderBy('created_at', 'desc');
    }


    public function map($ticket): array{


        if (isset($ticket->booking->employees) && $ticket->booking->employees->count()>0){

            foreach ($ticket->booking->employees as $mechanic){
              $mechanics[] =  $mechanic->name." ".$mechanic->surname; 
            }

            if (isset($mechanics)) {
                $assigned_to = implode(',' , $mechanics);
            }else {
                $assigned_to = "";
            }

            }elseif(isset($ticket->booking->vendor)){

            $assigned_to = ucfirst($ticket->booking->vendor->name);

            }else{
            $assigned_to = "";
            }

            $equipment_type = "";
            $ticket_for = "";

            if ($ticket->horse){
                $equipment_type = "Horse";
                $ticket_for = FleetIdentifier::workshopLabel($ticket->horse, $ticket->horse->horse_make ? $ticket->horse->horse_make->name : "", $ticket->horse->horse_model ? $ticket->horse->horse_model->name : "");
            }
            elseif($ticket->vehicle){
                $equipment_type = "Vehicle";
                $ticket_for = FleetIdentifier::workshopLabel($ticket->vehicle, $ticket->vehicle->vehicle_make ? $ticket->vehicle->vehicle_make->name : "", $ticket->vehicle->vehicle_model ? $ticket->vehicle->vehicle_model->name : "");
            }
            elseif($ticket->trailer){
                $equipment_type = "Trailer";
                $ticket_for = FleetIdentifier::workshopLabel($ticket->trailer, $ticket->trailer->make, $ticket->trailer->model);
            }


            $user = User::find($ticket->booking->authorized_by_id);

            if ($user) {
                $authorized_by = $user->name ." ". $user->surname;
            }else{
                $authorized_by = "";
            }
           
            $date = $ticket->in_date ." @ ". $ticket->in_time;
            
            $employee =  $ticket->booking->employee ? $ticket->booking->employee->name : "";

            return   [
                $ticket->ticket_number,
                $ticket->user->name ." ". $ticket->user->surname,
                $employee,
                $assigned_to,
                $equipment_type,
                $ticket_for,
                $ticket->service_type ? $ticket->service_type->name : "",
                $ticket->description,
                $date,
                $ticket->booking->authorization,
                $authorized_by,
                $ticket->status == 1 ? "Open" : "Closed",
                 ];


    }
    public function headings(): array{
            return[
                'Ticket#',
                'CreatedBy ',
                'RequestedBy',
                'AssignedTo',
                'Equipment Type',
                'Equipment',
                'Job Type',
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
                $event->sheet->getStyle('A7:L7')->applyFromArray([
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
