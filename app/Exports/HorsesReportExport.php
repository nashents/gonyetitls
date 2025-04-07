<?php

namespace App\Exports;

use App\Models\Trip;
use App\Models\Horse;
use App\Models\Currency;
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

class HorsesReportExport implements FromQuery,
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

    public $horses;
    public $filter;
    public $to;
    public $from;

    public function __construct($filter, $from, $to)
    {
            $this->filter = $filter;
            $this->from = $from;
            $this->to = $to;
           
    }
    public function query()
    {
        return Horse::query();
    }

    public function map($horse): array{

                $transporter =  $horse->transporter ? $horse->transporter->name : "";
                $horse_number =  $horse->horse_number;
                $horse_make =  $horse->horse_make ? $horse->horse_make->name : "";
                $horse_model = $horse->horse_model ? $horse->horse_model->name : "";
                $horse_registration_number = $horse->registration_number;
                if (isset($this->from) && isset($this->to)) {
                    $usd = Trip::where('currency_id',1)->where('horse_id',$horse->id)
                    ->whereBetween('start_date',[$this->from, $this->to] )->get()->sum('freight');
                    $zwl = Trip::where('currency_id',2)->where('horse_id',$horse->id)
                    ->whereBetween('start_date',[$this->from, $this->to] )->get()->sum('freight');
                    $rands = Trip::where('currency_id',3)->where('horse_id',$horse->id)
                    ->whereBetween('start_date',[$this->from, $this->to] )->get()->sum('freight');
                }else {
                    $usd = Trip::where('currency_id',1)->where('horse_id',$horse->id)->get()->sum('freight');
                    $zwl = Trip::where('currency_id',2)->where('horse_id',$horse->id)->get()->sum('freight');
                    $rands = Trip::where('currency_id',3)->where('horse_id',$horse->id)->get()->sum('freight');
                }
               


                return   [
                    $transporter ,
                    $horse_number ,
                    $horse_make ,
                    $horse_model ,
                    $usd ,
                    $zwl ,
                    $rands ,
                    // $currency->name .' '. $currency->symbol ." ". number_format($revenue,2),
                     ];
              

            //     if ($this->filter == "revenue") {
            //     $currencies = Currency::all();
            //     foreach ($currencies as $currency) {
            //         if (isset($this->from) && isset($this->to)) {
            //             $revenue = Trip::query()->where('horse_id',$horse->id)
            //             ->where('currency_id',$currency->id)->whereBetween('created_at',[$this->from, $this->to] )->sum('freight');
            //            if (isset($revenue) && $revenue > 0) {
                    
            //         }
              
            //        }else {
                   
            //         $revenue = Trip::query()->where('horse_id',$horse->id)
            //         ->where('currency_id',$currency->id)->sum('freight');
            //        if (isset($revenue) && $revenue > 0) {
            //         return   [
            //             $transporter ,
            //             $horse_number ,
            //             $horse_make ,
            //             $horse_model ,
            //             $horse_registration_number ,
            //             $currency->name .' '. $currency->symbol ." ". number_format($revenue,2),
            //              ];
            //     }
            //        }
              
                    
            //     }
            // }

    }

    public function headings(): array{
            return[
                'Transporter',
                'Horse#',
                'Make',
                'Model',
                'HRN',
                'USD',
                'ZWL',
                'RANDS',
                // 'Revenue'
            ];
    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:E7')->applyFromArray([
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
