<?php

namespace App\Http\Livewire\Vendors;

use Livewire\Component;
use App\Models\Currency;
use App\Models\Vendor;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\CreditorsExport;

class  Creditors extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    private $creditors;
    public $currencies;

    public function mount(){
        $this->currencies = Currency::all();
    }

    public function exportCreditorsCSV(Excel $excel){

        return $excel->download(new CreditorsExport, 'creditors_' .time().'.csv', Excel::CSV);
    }
    public function exportCreditorsPDF(Excel $excel){

        return $excel->download(new CreditorsExport, 'creditors_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportCreditorsExcel(Excel $excel){
        return $excel->download(new CreditorsExport, 'creditors_' .time().'.xlsx');
    }

    public function render()
    {
        return view('livewire.vendors.creditors',[
            'creditors' => Vendor::whereHas('bills', function ($query) {
                $query->where('authorization','approved')->where('balance', '>', 0);
            })->orderBy('name','asc')->paginate(10),
        ]);
    }
}
