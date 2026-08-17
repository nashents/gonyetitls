<?php

namespace App\Exports;

use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\Invoice;
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

class CustomersExport implements  FromQuery,
ShouldAutoSize,
WithMapping,
WithHeadings,
WithEvents,
WithDrawings,
WithCustomStartCell
{
    use Exportable;

    public $from;
    public $to;
    public $user;
    public $employee;
    public $company;
    public $department_names = [];
    public $role_names = [];
    public $canViewRevenue = false;

    public function __construct($from = null, $to = null)
    {
        $this->from = $from;
        $this->to = $to;

        $this->user = Auth::user();
        $this->employee = $this->user->employee;
        $this->company = $this->employee->company;

        foreach (($this->employee->departments ?? []) as $department) {
            $this->department_names[] = $department->name;
        }

        foreach (($this->user->roles ?? []) as $role) {
            $this->role_names[] = $role->name;
        }

        $this->canViewRevenue = $this->company->rates_managed_by_finance == 0
            || ($this->company->rates_managed_by_finance == 1
                && (in_array('Finance', $this->department_names) || in_array('Super Admin', $this->role_names)));
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        return Customer::query();
    }
    public function map($customer): array{

            $country =  $customer->country ?: "";
            $city =  $customer->city ?: "";
            $suburb =  $customer->suburb ?: "";
            $street_address =  $customer->street_address ?: "";
            $address = $street_address." ".$suburb." ".$city." ".$country;
            $vat = $customer->vat_number ?: "";
            $tin = $customer->tin_number ? " | ".$customer->tin_number : "";

            $row = [
                $customer->name,
                $customer->phonenumber,
                $customer->worknumber,
                $customer->email,
                $vat."".$tin,
                $address,
                 ];

            if ($this->canViewRevenue) {
                $tripsQuery = $customer->trips();
                $invoicesQuery = Invoice::where('customer_id', $customer->id);

                if ($this->from && $this->to) {
                    $tripsQuery->whereBetween('trips.created_at', [$this->from, $this->to]);
                    $invoicesQuery->whereBetween('date', [$this->from, $this->to]);
                }

                $row[] = number_format((float) $tripsQuery->sum('turnover'), 2);
                $row[] = number_format((float) $invoicesQuery->sum('total'), 2);
            }

            return $row;


    }
    public function headings(): array{
            $headings = [
                'Name ',
                'Phonenumber',
                'Worknumber',
                'Email',
                 'VAT|TIN#',
                'Address',
            ];

            if ($this->canViewRevenue) {
                $headings[] = 'Trip Revenue';
                $headings[] = 'Invoiced Revenue';
            }

            return $headings;


    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:I7')->applyFromArray([
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
