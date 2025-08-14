<?php

namespace App\Exports;

use App\Models\Trip;
use App\Models\Horse;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;
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

class HorsesPerformanceExport implements  FromQuery,
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
    public $filter;  
    public $currency;  
   

    public function __construct($from, $to, $filter)
    {
            $this->from = $from;
            $this->to = $to;
            $this->filter = $filter;
            $this->currency = Auth::user()->employee->company->currency;  
 
    }
    public function query()
    { 
        if (isset($this->from) && isset($this->to)) {
            return DB::table('trips')
                ->select(
                    'trips.horse_id',
                    DB::raw('count(*) as total_trips'),
                    DB::raw("
                        SUM(
                            CASE 
                                WHEN trips.starting_mileage IS NOT NULL AND trips.ending_mileage IS NOT NULL 
                                THEN trips.ending_mileage - trips.starting_mileage 
                                ELSE trips.distance 
                            END
                        ) as total_kilometers
                    "),
                    DB::raw("
                        SUM(
                            CASE 
                                WHEN trips.starting_hours IS NOT NULL AND trips.ending_hours IS NOT NULL 
                                THEN trips.ending_hours - trips.starting_hours 
                                ELSE trips.hours 
                            END
                        ) as total_hours
                    "),
                    DB::raw('sum(litreage_at_20) as total_volume'),
                    DB::raw('sum(weight) as total_tonnage'),
                    DB::raw('sum(delivery_notes.loaded_litreage_at_20 - delivery_notes.offloaded_litreage_at_20) as total_volume_loss'),
                    DB::raw('sum(delivery_notes.loaded_weight - delivery_notes.offloaded_weight) as total_tonnage_loss'),
                    DB::raw("
                        SUM(
                            CASE 
                                WHEN fuels.quantity IS NOT NULL THEN fuels.quantity
                                ELSE COALESCE(trips.trip_fuel, 0)
                            END
                        ) as total_fuel_quantity
                    "),
                    DB::raw('avg(trips.fuel_consumption_mileage) as avg_fuel_consumption_mileage'),
                    DB::raw('avg(trips.fuel_consumption_hours) as avg_fuel_consumption_hours')
                )
                ->join('delivery_notes', 'trips.id', '=', 'delivery_notes.trip_id')
                ->join('horses', 'trips.horse_id', '=', 'horses.id')
                ->leftJoin('fuels', 'trips.id', '=', 'fuels.trip_id')
                ->where('horses.archive', '=', false)
                ->where('trips.trip_status', '=', 'Offloaded')
                ->whereBetween('trips.'.$this->filter, [$this->from, $this->to])
                ->whereNull('trips.deleted_at')
                ->where('trips.authorization', 'approved')
                ->groupBy('trips.horse_id')
                ->orderByDesc('total_trips');
        } else {
            return DB::table('trips')
                ->select(
                    'trips.horse_id',
                    DB::raw('count(*) as total_trips'),
                    DB::raw("
                        SUM(
                            CASE 
                                WHEN trips.starting_mileage IS NOT NULL AND trips.ending_mileage IS NOT NULL 
                                THEN trips.ending_mileage - trips.starting_mileage 
                                ELSE trips.distance 
                            END
                        ) as total_kilometers
                    "),
                    DB::raw("
                        SUM(
                            CASE 
                                WHEN trips.starting_hours IS NOT NULL AND trips.ending_hours IS NOT NULL 
                                THEN trips.ending_hours - trips.starting_hours 
                                ELSE trips.hours 
                            END
                        ) as total_hours
                    "),
                    DB::raw('sum(litreage_at_20) as total_volume'),
                    DB::raw('sum(weight) as total_tonnage'),
                    DB::raw('sum(delivery_notes.loaded_litreage_at_20 - delivery_notes.offloaded_litreage_at_20) as total_volume_loss'),
                    DB::raw('sum(delivery_notes.loaded_weight - delivery_notes.offloaded_weight) as total_tonnage_loss'),
                    DB::raw("
                        SUM(
                            CASE 
                                WHEN fuels.quantity IS NOT NULL THEN fuels.quantity
                                ELSE COALESCE(trips.trip_fuel, 0)
                            END
                        ) as total_fuel_quantity
                    "),
                    DB::raw('avg(trips.fuel_consumption_mileage) as avg_fuel_consumption_mileage'),
                    DB::raw('avg(trips.fuel_consumption_hours) as avg_fuel_consumption_hours')
                )
                ->join('delivery_notes', 'trips.id', '=', 'delivery_notes.trip_id')
                ->join('horses', 'trips.horse_id', '=', 'horses.id')
                ->leftJoin('fuels', 'trips.id', '=', 'fuels.trip_id')
                ->where('horses.archive', '=', false)
                ->where('trips.trip_status', '=', 'Offloaded')
                ->whereYear('trips.'.$this->filter, date('Y'))
                ->whereMonth('trips.'.$this->filter, now()->month)
                ->whereNull('trips.deleted_at')
                ->where('trips.authorization', 'approved')
                ->groupBy('trips.horse_id')
                ->orderByDesc('total_trips');
        }
       
       
    }

          public function calculateVolumeLosses($selected_horse){

         $vol_loss_percentage = Null; 

        if (($selected_horse->total_volume_loss && is_numeric($selected_horse->total_volume_loss) && $selected_horse->total_volume_loss > 0) && ($selected_horse->total_volume && is_numeric($selected_horse->total_volume) && $selected_horse->total_volume > 0)) {
            $vol_loss_percentage = ($selected_horse->total_volume_loss / $selected_horse->total_volume ) * 100;
        }

         return $vol_loss_percentage ? $vol_loss_percentage."%" : "";
      
    }
    public function calculateTonnageLosses($selected_horse){
       
        $tonnage_loss_percentage = Null;

        if (($selected_horse->total_tonnage_loss && is_numeric($selected_horse->total_tonnage_loss)  && $selected_horse->total_tonnage_loss > 0) && ($selected_horse->total_tonnage && is_numeric($selected_horse->total_tonnage) && $selected_horse->total_tonnage > 0)) {
            $tonnage_loss_percentage = ($selected_horse->total_tonnage_loss / $selected_horse->total_tonnage ) * 100;
        }

        return $tonnage_loss_percentage ? $tonnage_loss_percentage."%" : "";
    }

    public function calculateTotalRevenue($id)
    {
        if (is_null($id)) return;

        if (!$this->currency) {
            return "Currency not set.";
        }

        $total_freight = Null;

        if ($this->from && $this->to) {
            $default_currency_trips_freight = Trip::where('horse_id', $id)
                ->whereBetween($this->filter, [$this->from, $this->to])
                ->where('currency_id', $this->currency->id)
                ->whereNotNull('freight')
                ->where('freight', '!=', 0)
                ->sum('freight');

            $other_currency_trips_freight = Trip::where('horse_id', $id)
                ->whereBetween($this->filter, [$this->from, $this->to])
                ->where('currency_id', '!=', $this->currency->id)
                ->whereNotNull('exchange_customer_freight')
                ->where('exchange_customer_freight', '!=', 0)
                ->sum('exchange_customer_freight');
        } else {
            $default_currency_trips_freight = Trip::where('horse_id', $id)
                ->whereMonth($this->filter, Carbon::now()->month)
                ->whereYear($this->filter, date('Y'))
                ->where('currency_id', $this->currency->id)
                ->whereNotNull('freight')
                ->where('freight', '!=', 0)
                ->sum('freight');

            $other_currency_trips_freight = Trip::where('horse_id', $id)
                ->whereMonth($this->filter, Carbon::now()->month)
                ->whereYear($this->filter, date('Y'))
                ->where('currency_id', '!=', $this->currency->id)
                ->whereNotNull('exchange_customer_freight')
                ->where('exchange_customer_freight', '!=', 0)
                ->sum('exchange_customer_freight');
        }

        $total_freight = $default_currency_trips_freight + $other_currency_trips_freight;

        return $this->currency->symbol . number_format($total_freight, 2);
    }

            // RAW total fuel
    private function getTotalFuel($id)
    {
        if (!isset($this->from, $this->to)) return 0;

        $dateColumn = $this->filter === "start_date" ? "date" : $this->filter;

        return Shift::whereBetween($dateColumn, [$this->from, $this->to])
            ->where('horse_id', $id)
            ->sum('total_fuel');
    }

    // RAW total distance
    private function getTotalDistance($id)
    {
        if (!isset($this->from, $this->to)) return 0;

        $dateColumn = $this->filter === "start_date" ? "date" : $this->filter;

        return Shift::whereBetween($dateColumn, [$this->from, $this->to])
            ->where('horse_id', $id)
            ->sum('actual_mileage');
    }
    // RAW total hours
    private function getTotalHours($id)
    {
        if (!isset($this->from, $this->to)) return 0;

        $dateColumn = $this->filter === "start_date" ? "date" : $this->filter;

        return Shift::whereBetween($dateColumn, [$this->from, $this->to])
            ->where('horse_id', $id)
            ->sum('actual_hours');
    }

    // Existing public display functions
    public function calculateShiftsFuel($id)
    {
        return number_format($this->getTotalFuel($id));
    }

    public function calculateShiftsDistance($id)
    {
        return number_format($this->getTotalDistance($id));
    }
    public function calculateShiftsHours($id)
    {
        return number_format($this->getTotalHours($id));
    }

    // NEW: Fuel consumption function (Km per L)
    public function calculateFuelConsumptionMileage($id)
    {
        $fuel = $this->getTotalFuel($id);
        $distance = $this->getTotalDistance($id);

        if ($fuel <= 0 || $distance <= 0) {
            return ;
        }

        $KPerL = $distance / $fuel; // or $fuel / $distance * 100 for L/100km
        return number_format($KPerL, 2);
    }
    public function calculateFuelConsumptionHours($id)
    {
        $fuel = $this->getTotalFuel($id);
        $hours = $this->getTotalHours($id);

        if ($fuel <= 0 || $hours <= 0) {
            return ;
        }

        $HPerL = $hours / $fuel; // or $fuel / $hours * 100 for L/100H
        return number_format($HPerL, 2);
    }


    public function map($selected_horse): array{

            $horse = Horse::find($selected_horse->horse_id);
            $regnumber = $horse->registration_number;
            $fleetnumber = $horse->fleet_number  ? "(".$horse->fleet_number.")" : "";
            $horse_details = $regnumber." ".$fleetnumber;
            $transporter = $horse->transporter ? $horse->transporter->name : "";

            $total_kilometers = "";
            if ($selected_horse->total_kilometers) {
                $total_kilometers = $selected_horse->total_kilometers;
            }else{
                 $total_kilometers = $this->calculateShiftsDistance($selected_horse->horse_id);
            }
            $total_hours = "";
            if ($selected_horse->total_hours) {
                $total_hours = $selected_horse->total_hours;
            }else{
                $total_hours = $this->calculateShiftsHours($selected_horse->horse_id);
            }
            $total_fuel_quantity = "";
            if ($selected_horse->total_fuel_quantity) {
                $total_fuel_quantity = $selected_horse->total_fuel_quantity;
            }else{
                $total_fuel_quantity = $this->calculateShiftsFuel($selected_horse->horse_id);
            }

            $tonnage_loss = $this->calculateTonnageLosses($selected_horse);
            $volume_loss = $this->calculateVolumeLosses($selected_horse);

            $fuel_consumption_mileage = "";

            if ($selected_horse->avg_fuel_consumption_mileage) {
                $fuel_consumption_mileage = $selected_horse->avg_fuel_consumption_mileage;
            }else{
                $fuel_consumption_mileage = $this->calculateFuelConsumptionMileage($selected_horse->horse_id);
            }
           
            $fuel_consumption_hours = "";

            if ($selected_horse->avg_fuel_consumption_hours) {
                $fuel_consumption_hours = $selected_horse->avg_fuel_consumption_hours;
            }else{
                $fuel_consumption_hours = $this->calculateFuelConsumptionHours($selected_horse->horse_id);
            }
         
                return   [
                    $transporter,
                    $horse_details,
                    $selected_horse->total_trips ,
                    $this->calculateTotalRevenue($selected_horse->horse_id),
                    $total_kilometers ,
                    $total_hours ,
                    $total_fuel_quantity,
                    $fuel_consumption_mileage,
                    $fuel_consumption_hours,
                    $selected_horse->total_volume,
                    $selected_horse->total_volume_loss,
                    $volume_loss,
                    $selected_horse->total_tonnage,
                    $selected_horse->total_tonnage_loss,
                    $tonnage_loss,
                     ];

    }

    public function headings(): array{
            return[
                'Transporter',
                'Horse',
                'Trips',
                'Revenue',
                'Dist(Km)',
                'Hours(H)',
                'Fuel(l)',
                'F/C Mileage(l/Km)',
                'F/C Hours(l/H)',
                'Vol(l)',
                'V/Loss(l)',
                'V/Loss(%)',
                'Weight',
                'W/Loss(t)',
                'W/Loss(%)',
            ];
    }
    
     public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:O7')->applyFromArray([
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
