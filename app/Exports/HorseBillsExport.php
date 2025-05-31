<?php

namespace App\Exports;

use App\Models\Bill;
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

class HorseBillsExport implements   FromQuery,
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
        return Bill::query()->with('vendor','container', 'horse', 'fuel','transporter','invoice','ticket','purchase')->where('horse_id',$this->horse_id)
        ->whereYear('created_at', date('Y'))->orderBy('created_at','desc');
         
     
    }
    public function map($bill): array{

        if ($bill->transporter){
            $bill_category = "Transporter | ".$bill->transporter ? $bill->transporter->name  : "";
        }elseif($bill->vendor){
            $name = $bill->vendor ? $bill->vendor->name : "";
            $vendor =  "Vendor | ".$name;
            if ($bill->horse) {
                $make = $bill->horse->horse_make ? $bill->horse->horse_make->name : "";
                $model = $bill->horse->horse_model ? $bill->horse->horse_model->name : "";
                $fleet_number = $bill->horse->fleet_number ? "(".$bill->horse->fleet_number.")" : "";
                $reg_number = $bill->horse->registration_number ? $bill->horse->registration_number : "";
                $horse = $reg_number." ".$fleet_number." ".$make." ".$model;
                $bill_category = $vendor.", Horse | ".$horse ;
            }elseif ($bill->vehicle) {
                $make = $bill->vehicle->vehicle_make ? $bill->vehicle->vehicle_make->name : "";
                $model = $bill->vehicle->vehicle_model ? $bill->vehicle->vehicle_model->name : "";
                $fleet_number = $bill->vehicle->fleet_number ? "(".$bill->vehicle->fleet_number.")" : "";
                $reg_number = $bill->vehicle->registration_number ? $bill->vehicle->registration_number : "";
                $vehicle = $reg_number." ".$fleet_number." ".$make." ".$model;
                $bill_category = $vendor.", Vehicle | ".$vehicle ;
            }elseif ($bill->trailer) {
                $make = $bill->trailer->make;
                $model = $bill->trailer->model;
                $fleet_number = $bill->trailer->fleet_number ? "(".$bill->trailer->fleet_number.")" : "";
                $reg_number = $bill->trailer->registration_number ? $bill->trailer->registration_number : "";
                $trailer = $reg_number." ".$fleet_number." ".$make." ".$model;
                $bill_category = $vendor.", Trailer | ".$trailer ;
            }elseif ($bill->driver) {
                $name = $bill->driver->employee ? $bill->driver->employee->name : "";
                $surname = $bill->driver->employee ? $bill->driver->employee->surname : "";
                $driver = $name." ".$surname;
                $bill_category = $vendor.", Driver | ".$driver ;
            }
         
        }elseif ( $bill->container && $bill->top_up){
            $bill_categoty =  "Fuel Topup | ".$bill->container ? $bill->container->name : "" ;
        }elseif ( $bill->fuel){
            if ($bill->trip){
                $bill_category =   "Trip Expense - Fuel Order | ". $bill->fuel ? $bill->fuel->order_number : "" . $bill->trip->trip_number;
            }{
                $bill_category =  "Fuel Order | ".$bill->fuel ? $bill->fuel->order_number : ""; 
            }
        }elseif ( $bill->invoice){
            $bill_category =    "Invoice VAT |".  $bill->invoice ? $bill->invoice->invoice_number : "" ;
        }elseif ($bill->ticket || $bill->ticket_inventory || $bill->ticket_expense){
                        if ($bill->ticket) {
                            $ticket = $bill->ticket ? $bill->ticket->ticket_number : "";
                           $bill_category =    "Ticket | " . $ticket;
                        }elseif ($bill->ticket_inventory) {
                             $ticket =$bill->ticket_inventory->ticket ? $bill->ticket_inventory->ticket->ticket_number : "";
                           $bill_category =    "Ticket | ".$ticket;
                        }elseif ($bill->ticket_expense) {
                             $ticket = $bill->ticket_expense->ticket ? $bill->ticket_expense->ticket->ticket_number : "";
                           $bill_category =    "Ticket | ".$ticket;
                        }
                       
                    }elseif ($bill->trip){
            $bill_category =   "Trip Expense | ".$bill->trip->trip_number;
        }elseif ($bill->purchase){
            $bill_category =   $bill->category." | ". $bill->purchase->purchase_number;
        }elseif ($bill->workshop_service){
            $bill_category =  "Service | ".$bill->workshop_service->account ? $bill->workshop_service->account->name : "" ." | ". $bill->workshop_service->workshop_service_number; 
        } elseif ($bill->horse && !$bill->vendor) {
            $make = $bill->horse->horse_make ? $bill->horse->horse_make->name : "";
            $model = $bill->horse->horse_model ? $bill->horse->horse_model->name : "";
            $fleet_number = $bill->horse->fleet_number ? "(".$bill->horse->fleet_number.")" : "";
            $reg_number = $bill->horse->registration_number ? $bill->horse->registration_number : "";
            $horse = $reg_number." ".$fleet_number." ".$make." ".$model;
            $bill_category = "Horse | ".$horse ;
        }elseif ($bill->vehicle && !$bill->vendor) {
            $make = $bill->vehicle->vehicle_make ? $bill->vehicle->vehicle_make->name : "";
            $model = $bill->vehicle->vehicle_model ? $bill->vehicle->vehicle_model->name : "";
            $fleet_number = $bill->vehicle->fleet_number ? "(".$bill->vehicle->fleet_number.")" : "";
            $reg_number = $bill->vehicle->registration_number ? $bill->vehicle->registration_number : "";
            $vehicle = $reg_number." ".$fleet_number." ".$make." ".$model;
            $bill_category = "Vehicle | ".$vehicle ;
        }elseif ($bill->trailer && !$bill->vendor) {
            $make = $bill->trailer->make;
            $model = $bill->trailer->model;
            $fleet_number = $bill->trailer->fleet_number ? "(".$bill->trailer->fleet_number.")" : "";
            $reg_number = $bill->trailer->registration_number ? $bill->trailer->registration_number : "";
            $trailer = $reg_number." ".$fleet_number." ".$make." ".$model;
            $bill_category = "Trailer | ".$trailer ;
        }elseif ($bill->driver && !$bill->vendor) {
            $name = $bill->driver->employee ? $bill->driver->employee->name : "";
            $surname = $bill->driver->employee ? $bill->driver->employee->surname : "";
            $driver = $name." ".$surname;
            $bill_category = "Driver | ".$driver ;
        }
        else{
            $bill_category = "";
        }

    $symbol = $bill->currency ? $bill->currency->symbol : "";
    $currency = $bill->currency ? $bill->currency->name : "";
    $subtotal =  number_format($bill->subtotal ? $bill->subtotal : 0,2);
    $tax_amount =    number_format($bill->tax_amount ? $bill->tax_amount : 0,2);
    $total =  number_format($bill->total,2);
    if (isset($bill->payments)){
    $payments =  number_format($bill->payments->sum('amount'),2);
    }else{
    $payments = number_format($bill->bill_payments->sum('amount'),2);
    }
    $balance =  number_format($bill->balance,2);

    if ($bill->bill_expenses) {
        foreach ($bill->bill_expenses as $expense) {
                        if ($expense->product) {
                            $items[] = $expense->product ? $expense->product->name : "";
                        }elseif($expense->expense){
                            $items[] = $expense->expense ? $expense->expense->name : "";
                        }elseif($expense->inventory){
                            $items[] = $expense->inventory->product ? $expense->inventory->product->name : "";
                        }
                     
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
        $bill->bill_number ,
        $bill_category,
        $items_list,
        $bill->bill_date,
        $bill->due_date,
        $bill->status,
        $currency.' '.$symbol,
        $subtotal ?  $subtotal : $total,
        $tax_amount,
        $total,
        $payments,
        $balance,
     
         ];
    }
    public function headings(): array{
            return[
                'Bill#',
                'Bill Summary',
                'Item(s)',
                'Date',
                'Due',
                'Status',
                'Currency',
                'Subtotal',
                'Tax Amt',
                'Total',
                'Paid',
                'Balance',
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
