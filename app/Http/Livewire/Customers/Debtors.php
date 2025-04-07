<?php

namespace App\Http\Livewire\Customers;

use Livewire\Component;
use App\Models\Currency;
use App\Models\Customer;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\DebtorsExport;

class Debtors extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    private $debtors;
    public $currencies;

    public function mount(){
        $this->currencies = Currency::all();
    }

    public function exportDebtorsCSV(Excel $excel){

        return $excel->download(new DebtorsExport, 'debtors_' .time().'.csv', Excel::CSV);
    }
    public function exportDebtorsPDF(Excel $excel){

        return $excel->download(new DebtorsExport, 'debtors_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportDebtorsExcel(Excel $excel){
        return $excel->download(new DebtorsExport, 'debtors_' .time().'.xlsx');
    }

    public function render()
    {
        return view('livewire.customers.debtors',[
            'debtors' => Customer::whereHas('invoices', function ($query) {
                $query->where('balance', '>', 0);
            })->orderBy('name','asc')->paginate(10),
        ]);
    }
}
