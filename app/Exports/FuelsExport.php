<?php

namespace App\Exports;

use App\Models\Fuel;
use App\Models\User;
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

class FuelsExport implements  FromQuery,
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
    public $fuel_filter;
    public $container_id;
   

    public function __construct($from, $to, $fuel_filter, $container_id)
    {
            $this->from = $from;
            $this->to = $to;
            $this->fuel_filter = $fuel_filter;
            $this->container_id = $container_id;
           
    }
    public function query()
    {
        if (isset($this->from) && isset($this->to)) {
            if (isset($this->container_id)) {
                return Fuel::query()->with(['container','horse','source_horse',
                'horse.horse_make','horse.horse_model','vehicle.vehicle_make','vehicle.vehicle_model','asset.product','trip.horse.horse_make','trip.horse.horse_model','user','currency'])->where('container_id',$this->container_id)->whereBetween($this->fuel_filter,[$this->from, $this->to] )->orderBy('created_at','desc');
            }else{
                return Fuel::query()->with(['container','horse','source_horse',
                'horse.horse_make','horse.horse_model','vehicle.vehicle_make','vehicle.vehicle_model','asset.product','trip.horse.horse_make','trip.horse.horse_model','user','currency'])->whereBetween($this->fuel_filter,[$this->from, $this->to] )->orderBy('created_at','desc');
            }

        }elseif(isset($this->container_id)){
            return Fuel::query()->with(['container','horse','source_horse',
            'horse.horse_make','horse.horse_model','vehicle.vehicle_make','vehicle.vehicle_model','asset.product','trip.horse.horse_make','trip.horse.horse_model','user','currency'])->where('container_id',$this->container_id)->whereMonth($this->fuel_filter, date('m'))
            ->whereYear($this->fuel_filter, date('Y'))->orderBy('created_at','desc');
        }else {
            return Fuel::query()->with(['container','horse','source_horse',
            'horse.horse_make','horse.horse_model','vehicle.vehicle_make','vehicle.vehicle_model','asset.product','trip.horse.horse_make','trip.horse.horse_model','user','currency'])->whereMonth($this->fuel_filter, date('m'))
            ->whereYear($this->fuel_filter, date('Y'))->orderBy('created_at','desc');
        }
    }
    public function map($fuel): array{

        $horse = $fuel->horse ?? optional($fuel->trip)->horse;
        $equipment = null;
        $makeModel = '';

        if (in_array($fuel->type, ['Horse', 'Trip']) && $horse) {
            $equipment = $horse;
            $makeModel = trim(optional($horse->horse_make)->name . ' ' . optional($horse->horse_model)->name);
        } elseif ($fuel->type == "Vehicle" && $fuel->vehicle) {
            $equipment = $fuel->vehicle;
            $makeModel = trim(optional($fuel->vehicle->vehicle_make)->name . ' ' . optional($fuel->vehicle->vehicle_model)->name);
        } elseif ($fuel->type == "Asset" && $fuel->asset) {
            $makeModel = optional($fuel->asset->product)->name ?? '';
        }

        $category = trim(($fuel->trip ? "Trip#: " . $fuel->trip->trip_number . ' ' : '') . $makeModel);
        $authorizedBy = $fuel->authorized_by_id ? User::find($fuel->authorized_by_id) : null;
        $source = $fuel->source_horse
            ? $fuel->source_horse->identifier_label
            : optional($fuel->container)->name;

        return [
            $fuel->order_number,
            trim(optional($fuel->user)->name . ' ' . optional($fuel->user)->surname),
            $fuel->created_at ? $fuel->created_at->format('Y-m-d H:i') : '',
            $fuel->authorization,
            $authorizedBy ? trim($authorizedBy->name . ' ' . $authorizedBy->surname) : '',
            $source ?? '',
            $fuel->date,
            $fuel->type,
            // reg# / fleet# in the order set in Business Settings
            $equipment ? $equipment->identifier_label : '',
            $category,
            in_array($fuel->type, ['Asset', 'Other']) ? '' : $fuel->odometer,
            $fuel->fillup == 1 ? "initial" : "top up",
            $fuel->container ? $fuel->container->fuel_type : "",
            $fuel->quantity. 'L',
            $fuel->currency ? $fuel->currency->name : "",
            ($fuel->currency ? $fuel->currency->symbol : "") . $fuel->amount,
            $fuel->comments,
        ];
    }

    public function headings(): array{
            return[
                'Order#',
                'Created By',
                'Created On',
                'Auth Status',
                'Authorized By',
                'Source',
                'Date',
                'Fuel Order For',
                'Equipment',
                'Category',
                'Mileage(Kms)',
                'Fuel Order Type',
                'Fuel Type',
                'Quantity',
                'Currency',
                'Amount',
                'Comments',
               
            ];


    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:Q7')->applyFromArray([
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
