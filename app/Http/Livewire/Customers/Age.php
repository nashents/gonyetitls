<?php

namespace App\Http\Livewire\Customers;

use Livewire\Component;
use App\Models\Customer;
use Maatwebsite\Excel\Excel;
use App\Exports\CustomersAgeExport;

class Age extends Component
{
    public $customers;

    public function mount(){
        $this->customers = Customer::orderBy('name','asc')->get();
    }

    public function exportCustomersAgeCSV(Excel $excel){

        return $excel->download(new CustomersAgeExport, 'customers.csv', Excel::CSV);
    }
    public function exportCustomersAgePDF(Excel $excel){

        return $excel->download(new CustomersAgeExport, 'customers.pdf', Excel::DOMPDF);
    }
    public function exportCustomersAgeExcel(Excel $excel){
        return $excel->download(new CustomersAgeExport, 'customers.xlsx');
    }

    public function render()
    {
        return view('livewire.customers.age');
    }
}
