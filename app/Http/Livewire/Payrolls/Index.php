<?php

namespace App\Http\Livewire\Payrolls;

use App\Models\User;
use App\Models\Salary;
use App\Models\Payroll;
use App\Models\PayrollFrequency;
use App\Models\PayrollRun;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Payroll\PayrollBatchService;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    
    public $payroll;
    protected $payrolls;
    public $payroll_id;
    public $payroll_number;
    public $month;
    public $year;
    public $selectedCurrency;
    public $currencies;
    public $salaries;
    public $selected_payroll;
    public $company;

    public function mount(){
        $this->company  = Auth::user()->employee?->company;
        $this->salaries = Salary::with(['employee', 'salary_items'])
            ->where('status', 1)
            ->when($this->company, fn ($q) => $q->whereHas('employee', fn ($eq) => $eq->where('company_id', $this->company->id)))
            ->get();
    }

    /**
     * Find (or create, for companies that predate the seeder) the company's
     * default monthly PayrollFrequency, used when this legacy form derives
     * a PayrollRun header from plain month/year inputs.
     */
    private function defaultFrequency(): PayrollFrequency
    {
        return PayrollFrequency::where('code', 'MONTHLY')
            ->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $this->company?->id))
            ->first()
            ?? PayrollFrequency::firstOrCreate(
                ['code' => 'MONTHLY'],
                ['name' => 'Monthly', 'periods_per_year' => 12, 'active' => true]
            );
    }


    private function resetInputFields(){
        $this->payroll_number = '';
        $this->month = '';
        $this->year = '';
    }

    /**
     * Derive (or find) the PayrollRun header backing this month/year, then
     * delegate the actual batch-build to PayrollBatchService — the same
     * service the new /payroll-runs UI uses, so there's one implementation
     * of "snapshot active salaries into a payroll batch", not two.
     */
    public function store(){
        if (!$this->company) {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'Your user account is not linked to an employee record with a company.',
            ]);
            return;
        }

        $periodStart = \Carbon\Carbon::parse($this->month . ' 1, ' . $this->year)->startOfMonth();
        $periodEnd   = $periodStart->copy()->endOfMonth();

        $run = PayrollRun::create([
            'company_id'           => $this->company->id,
            'payroll_frequency_id' => $this->defaultFrequency()->id,
            'currency_id'          => $this->company->currency_id,
            'name'                 => $this->month . ' ' . $this->year . ' Payroll',
            'period_start'         => $periodStart,
            'period_end'           => $periodEnd,
            'payroll_date'         => $periodEnd,
            'status'               => 'draft',
            'created_by'           => Auth::id(),
        ]);

        app(PayrollBatchService::class)->buildBatch($run, $this->salaries ?? collect());

        $this->dispatchBrowserEvent('hide-payrollModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Payroll Created Successfully!!"
        ]);


    }

     public function getAuthorizer($id){
        if(is_null($id)){
            return ;
        }
        $user = User::find($id);
        return $user?->name." ".$user?->surname;
    }

    public function edit($id){
        $payroll = Payroll::find($id);
        $this->payroll_id = $id;
        $this->month = $payroll->month;
        $this->year = $payroll->year;
       
        $this->dispatchBrowserEvent('show-payrollEditModal');
    }

    public function update(){
        $payroll = Payroll::find($this->payroll_id);

        // Lock check: approved payrolls are immutable
        if ($payroll && $payroll->authorization === 'approved') {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'This payroll has been approved and cannot be edited.',
            ]);
            $this->dispatchBrowserEvent('hide-payrollEditModal');
            return;
        }

        $this->authorize('update', $payroll);

        $payroll->month = $this->month;
        $payroll->year = $this->year;
        $payroll->update();

        $periodStart = \Carbon\Carbon::parse($this->month . ' 1, ' . $this->year)->startOfMonth();
        $periodEnd   = $periodStart->copy()->endOfMonth();

        $run = $payroll->payrollRun;
        if ($run) {
            $run->update(['period_start' => $periodStart, 'period_end' => $periodEnd, 'payroll_date' => $periodEnd]);
        } else {
            // Legacy payroll predating the PayrollRun bridge — retrofit one now.
            $run = PayrollRun::create([
                'company_id'           => $this->company?->id ?? Auth::user()->employee?->company_id,
                'payroll_frequency_id' => $this->defaultFrequency()->id,
                'currency_id'          => $payroll->currency_id,
                'name'                 => $this->month . ' ' . $this->year . ' Payroll',
                'period_start'         => $periodStart,
                'period_end'           => $periodEnd,
                'payroll_date'         => $periodEnd,
                'status'               => 'draft',
                'created_by'           => Auth::id(),
            ]);
            $payroll->update(['payroll_run_id' => $run->id]);
        }

        app(PayrollBatchService::class)->buildBatch($run, $this->salaries ?? collect());

        $this->dispatchBrowserEvent('hide-payrollEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Payroll Updated Successfully!!"
        ]);


    }

    public function delete($id){
        $payroll = Payroll::find($id);
        $this->selected_payroll = $payroll;
        $this->payroll_id = $id;
        $this->dispatchBrowserEvent('show-payrollDeleteModal');
    }

    public function destroy(){
        $payroll = Payroll::find($this->payroll_id);

        // Lock check: approved payrolls cannot be deleted
        if ($payroll && $payroll->authorization === 'approved') {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'Approved payrolls cannot be deleted. Reverse the payroll first.',
            ]);
            $this->dispatchBrowserEvent('hide-payrollDeleteModal');
            return;
        }

        $this->authorize('delete', $payroll);

        $payroll_salaries = $payroll->payroll_salaries;

        if ($payroll_salaries) {
            foreach ($payroll_salaries as $payroll_salary) {

                $payroll_salary_items = $payroll_salary->payroll_salary_items;

                if ($payroll_salary_items) {
                    foreach ($payroll_salary_items as $item) {
                       $item->delete();
                    }
                }

                $payroll_salary->delete();
            }
        }
        $payroll->delete();

        $this->dispatchBrowserEvent('hide-payrollDeleteModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Payroll Deleted Successfully!!"
        ]);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }


    public function render()
    {

        $base = Payroll::query()
        ->when($this->search, function ($q) {
            $term  = trim($this->search);
            $lower = strtolower($term);

            // Maps
            $numToFull = [
                1  => 'January',
                2  => 'February',
                3  => 'March',
                4  => 'April',
                5  => 'May',
                6  => 'June',
                7  => 'July',
                8  => 'August',
                9  => 'September',
                10 => 'October',
                11 => 'November',
                12 => 'December',
            ];

            $abbrToFull = [
                'jan'       => 'January',
                'january'   => 'January',
                'feb'       => 'February',
                'february'  => 'February',
                'mar'       => 'March',
                'march'     => 'March',
                'apr'       => 'April',
                'april'     => 'April',
                'may'       => 'May',
                'jun'       => 'June',
                'june'      => 'June',
                'jul'       => 'July',
                'july'      => 'July',
                'aug'       => 'August',
                'august'    => 'August',
                'sep'       => 'September',
                'sept'      => 'September',
                'september' => 'September',
                'oct'       => 'October',
                'october'   => 'October',
                'nov'       => 'November',
                'november'  => 'November',
                'dec'       => 'December',
                'december'  => 'December',
            ];

            $monthName = null;

            // Numeric month: 1, 01, 12, etc.
            if (preg_match('/^(0?[1-9]|1[0-2])$/', $term)) {
                $num = (int) $term;
                $monthName = $numToFull[$num] ?? null;
            }

            // Text month: Jan, January, etc.
            if (!$monthName && isset($abbrToFull[$lower])) {
                $monthName = $abbrToFull[$lower];
            }

            $q->where(function ($sub) use ($term, $monthName) {
                // 1) Match user name/surname
                $sub->whereHas('user', function ($uq) use ($term) {
                    $uq->where('name', 'like', '%'.$term.'%')
                    ->orWhere('surname', 'like', '%'.$term.'%');
                });

                // 2) If it's a 4-digit year, match year column
                if (preg_match('/^\d{4}$/', $term)) {
                    $sub->orWhere('year', $term);
                }

                // 3) Month name handling on string month column
                if ($monthName) {
                    // Column holds full month string, e.g. "January"
                    $sub->orWhere('month', 'like', '%'.$monthName.'%');
                } else {
                    // Fallback: if DB has "January" and user types "Jan" or similar
                    $sub->orWhere('month', 'like', '%'.$term.'%');
                }
            });
        })
        ->orderBy('created_at', 'desc')
        ->paginate(10);
       
        return view('livewire.payrolls.index',[
            'payrolls' => $base
        ]);
    }
}
