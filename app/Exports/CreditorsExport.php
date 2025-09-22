<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Currency;
use App\Models\Vendor;
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

class CreditorsExport implements  FromQuery,
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
        return Vendor::query()->whereHas('bills', function ($query) {
            $query->where('authorization','approved')->where('balance', '>', 0);
        })->orderBy('name','asc');
    }
    public function map($vendor): array{

        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $thirtyOneDaysAgo = Carbon::now()->subDays(31);
        $sixtyDaysAgo = Carbon::now()->subDays(60);
        $sixtyOneDaysAgo = Carbon::now()->subDays(61);
        $ninetyDaysAgo = Carbon::now()->subDays(90);
        $currencies = Currency::all();

        foreach ($currencies as $currency){
            $total_balance = $vendor->bills->where('authorization','approved')->where('currency_id',$currency->id)->where('balance','!=','')->where('balance','!=', Null)->sum('balance');
            if (isset($total_balance) && $total_balance > 0) {
                $total_balances[] = $currency->name .' '.$currency->symbol.number_format($total_balance,2);
            }
         }
            if (isset($total_balances)) {
                $total_balances_list = implode(' ',$total_balances);
            }else{
                $total_balances_list = "";
            }

        foreach ($currencies as $currency){
            $thirty_days_balance = $vendor->bills->where('authorization','approved')->where('currency_id',$currency->id)->where('balance','!=','')->where('balance','!=', Null)->where('bill_date', '>=', $thirtyDaysAgo)->sum('balance');
            if (isset($thirty_days_balance) && $thirty_days_balance > 0) {
                $thirty_days_balances[] = $currency->name .' '.$currency->symbol.number_format($thirty_days_balance,2);
            }
         }
            if (isset($thirty_days_balances)){ 
                $thirty_days_balances_list = implode(' ',$thirty_days_balances);
            }else {
                $thirty_days_balances_list = "";
            }

        foreach ($currencies as $currency){
            $thirty_sixty_balance = $vendor->bills->where('authorization','approved')->where('currency_id',$currency->id)->where('balance','!=','')->where('balance','!=', Null)->whereBetween('bill_date', [$sixtyDaysAgo, $thirtyOneDaysAgo])->sum('balance');
            if (isset($thirty_sixty_balance) && $thirty_sixty_balance > 0) {
                $thirty_sixty_balances[] = $currency->name .' '.$currency->symbol.number_format($thirty_sixty_balance,2);
            }
           
         }
            if (isset($thirty_sixty_balances)){ 
                $thirty_sixty_balances_list = implode(' ',$thirty_sixty_balances);
            }else {
                $thirty_sixty_balances_list = "";
            }

        foreach ($currencies as $currency){
            $sixty_ninety_balance = $vendor->bills->where('authorization','approved')->where('currency_id',$currency->id)->where('balance','!=','')->where('balance','!=', Null)->whereBetween('bill_date', [$ninetyDaysAgo, $sixtyOneDaysAgo])->sum('balance');
            if (isset($sixty_ninety_balance) && $sixty_ninety_balance > 0) {
                $sixty_ninety_balances[] = $currency->name .' '.$currency->symbol.number_format($sixty_ninety_balance,2);
            }
         }
            if (isset($sixty_ninety_balances)){ 
                $sixty_ninety_balances_list = implode(' ',$sixty_ninety_balances);
            }else {
                $sixty_ninety_balances_list = "";
            }

        foreach ($currencies as $currency){
            $ninety_balance = $vendor->bills->where('authorization','approved')->where('currency_id',$currency->id)->where('balance','!=','')->where('balance','!=', Null)->where('bill_date', '<', $ninetyDaysAgo)->sum('balance');
            if (isset($ninety_balance) && $ninety_balance > 0) {
                $ninety_balances[] = $currency->name .' '.$currency->symbol.number_format($ninety_balance,2);
         }
            }
           
            if (isset($ninety_balances)){ 
                $ninety_balances_list = implode(' ',$ninety_balances);
            }else {
                $ninety_balances_list = "";
            }

            return   [
                $vendor->name,
                $total_balances_list,
                $thirty_days_balances_list,
                $thirty_sixty_balances_list,
                $sixty_ninety_balances_list,
                $ninety_balances_list,
                 ];


    }
    public function headings(): array{
            return[
                'Vendor',
                'Total',
                '0-30 Days',
                '31-60 Days',
                '61-90 Days',
                '>90 Days',
               
            ];


    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:F7')->applyFromArray([
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
