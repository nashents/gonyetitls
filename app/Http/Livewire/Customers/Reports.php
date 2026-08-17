<?php

namespace App\Http\Livewire\Customers;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Invoice;
use App\Exports\CustomersExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Excel;

class Reports extends Component
{

    public $search = NULL;
    public $to;
    public $from;
    public $customers;

    public $user;
    public $employee;
    public $company;
    public $department_names = [];
    public $role_names = [];
    public $canViewRevenue = false;

    public function mount()
    {
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

    public function search(){

        $this->search = TRUE;
        $this->customers = Customer::whereBetween('created_at',[$this->from, $this->to] )
        ->latest()->get();

        if ($this->canViewRevenue) {
            foreach ($this->customers as $customer) {
                $customer->trip_revenue = (float) $customer->trips()
                    ->whereBetween('trips.created_at', [$this->from, $this->to])
                    ->sum('turnover');

                $customer->invoiced_revenue = (float) Invoice::where('customer_id', $customer->id)
                    ->whereBetween('date', [$this->from, $this->to])
                    ->sum('total');
            }
        }

    }

    public function exportCustomersCSV(Excel $excel){
        return $excel->download(new CustomersExport($this->from, $this->to), 'customers.csv', Excel::CSV);
    }
    public function exportCustomersPDF(Excel $excel){
        return $excel->download(new CustomersExport($this->from, $this->to), 'customers.pdf', Excel::DOMPDF);
    }
    public function exportCustomersExcel(Excel $excel){
        return $excel->download(new CustomersExport($this->from, $this->to), 'customers.xlsx');
    }

    public function render()
    {
        return view('livewire.customers.reports');
    }
}
