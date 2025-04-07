<?php

namespace App\Http\Livewire\Vendors;

use App\Models\Vendor;
use Livewire\Component;
use Maatwebsite\Excel\Excel;
use App\Exports\VendorsAgeExport;

class Age extends Component
{

    public $vendors;

    public function mount(){
        $this->vendors = Vendor::orderBy('name','asc')->get();
    }

    public function exportVendorsAgeCSV(Excel $excel){

        return $excel->download(new VendorsAgeExport, 'vendors.csv', Excel::CSV);
    }
    public function exportVendorsAgePDF(Excel $excel){

        return $excel->download(new VendorsAgeExport, 'vendors.pdf', Excel::DOMPDF);
    }
    public function exportVendorsAgeExcel(Excel $excel){
        return $excel->download(new VendorsAgeExport, 'vendors.xlsx');
    }

    public function render()
    {
        return view('livewire.vendors.age');
    }
}
