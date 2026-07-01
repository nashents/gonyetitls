<?php

namespace App\Http\Livewire\SalaryAdvances;

use App\Models\Currency;
use App\Models\Employee;
use App\Models\SalaryAdvance;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search       = '';
    public $filterStatus = '';
    public $filterAuth   = '';
    protected $queryString = ['search', 'filterStatus', 'filterAuth'];

    // Create form
    public $showCreateModal  = false;
    public $selectedEmployee;
    public $selectedCurrency;
    public $amount;
    public $monthly_recovery_amount = 0;
    public $advance_date;
    public $repayment_start_date;
    public $reason;
    public $notes;

    // Approve/Reject
    public $showAuthModal    = false;
    public $authAdvanceId;
    public $authAction;
    public $authComments;

    // Support data
    public $employees;
    public $currencies;
    public $company;

    // Role flags
    public $isHR;
    public $isFinance;

    public function mount()
    {
        $this->company = Auth::user()->employee?->company;
        if (!$this->company) {
            abort(422, 'Your user account is not linked to an employee record with a company, so salary advances are unavailable.');
        }
        $this->currencies = Currency::orderBy('name')->get();
        $this->selectedCurrency = $this->company->currency_id;
        $this->advance_date = now()->toDateString();

        $user      = Auth::user();
        $roleNames = $user->roles->pluck('name');
        $deptNames = $user->employee?->departments->pluck('name') ?? collect();

        $this->isHR      = $deptNames->contains('Human Resources') || $roleNames->contains('Super Admin') || $roleNames->contains('Admin');
        $this->isFinance = $deptNames->contains('Finance') || $deptNames->contains('Management') || $roleNames->contains('Super Admin') || $roleNames->contains('Admin');

        $this->employees = Employee::with('user')
            ->where('company_id', $this->company->id)
            ->whereHas('user', fn($q) => $q->where('category', '!=', 'admin'))
            ->orderBy('name')->orderBy('surname')
            ->get();
    }

    // ── Create ──────────────────────────────────────────────────────────────

    public function openCreateModal()
    {
        $this->reset(['selectedEmployee','amount','monthly_recovery_amount','advance_date','repayment_start_date','reason','notes']);
        $this->selectedCurrency = $this->company->currency_id;
        $this->advance_date = now()->toDateString();
        $this->monthly_recovery_amount = 0;
        $this->showCreateModal = true;
    }

    public function store()
    {
        $this->validate([
            'selectedEmployee'       => 'required|exists:employees,id',
            'selectedCurrency'       => 'required|exists:currencies,id',
            'amount'                 => 'required|numeric|min:0.01',
            'advance_date'           => 'required|date',
            'reason'                 => 'required|string|max:255',
            'monthly_recovery_amount'=> 'nullable|numeric|min:0',
        ]);

        // Auto-generate advance number
        $last   = SalaryAdvance::orderByDesc('id')->first();
        $number = 'ADV-' . str_pad(($last?->id ?? 0) + 1, 5, '0', STR_PAD_LEFT);

        SalaryAdvance::create([
            'employee_id'             => $this->selectedEmployee,
            'company_id'              => $this->company->id,
            'currency_id'             => $this->selectedCurrency,
            'advance_number'          => $number,
            'amount'                  => $this->amount,
            'balance'                 => $this->amount,
            'monthly_recovery_amount' => $this->monthly_recovery_amount ?: 0,
            'advance_date'            => $this->advance_date,
            'repayment_start_date'    => $this->repayment_start_date,
            'reason'                  => $this->reason,
            'notes'                   => $this->notes,
            'authorization'           => 'pending',
            'status'                  => 'Unpaid',
            'created_by'              => Auth::id(),
        ]);

        $this->showCreateModal = false;
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Salary advance submitted for approval.']);
    }

    // ── Approve / Reject ─────────────────────────────────────────────────

    public function openAuthModal($id, $action)
    {
        $this->authAdvanceId = $id;
        $this->authAction    = $action;
        $this->authComments  = '';
        $this->showAuthModal = true;
    }

    public function authorise()
    {
        $advance = SalaryAdvance::findOrFail($this->authAdvanceId);

        if ($this->authAction === 'approve') {
            $advance->update([
                'authorization'          => 'approved',
                'authorized_by'          => Auth::id(),
                'authorized_at'          => now(),
                'authorization_comments' => $this->authComments,
            ]);
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Advance approved.']);
        } else {
            $advance->update([
                'authorization'          => 'rejected',
                'authorized_by'          => Auth::id(),
                'authorized_at'          => now(),
                'authorization_comments' => $this->authComments,
            ]);
            $this->dispatchBrowserEvent('alert', ['type' => 'warning', 'message' => 'Advance rejected.']);
        }

        $this->showAuthModal = false;
    }

    // ── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $query = SalaryAdvance::with(['employee', 'currency', 'authorizedBy'])
            ->where('company_id', $this->company->id);

        // Employees see only their own
        if (!$this->isHR && !$this->isFinance) {
            $query->where('employee_id', Auth::user()->employee?->id);
        }

        $query->when($this->search, fn($q) => $q->whereHas('employee', fn($eq) =>
            $eq->where('name','like',"%{$this->search}%")->orWhere('surname','like',"%{$this->search}%")
        ));
        $query->when($this->filterAuth,   fn($q) => $q->where('authorization', $this->filterAuth));
        $query->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus));

        $advances = $query->orderByDesc('advance_date')->paginate(15);

        // Totals for management view
        $totalGranted    = $query->clone()->where('authorization','approved')->sum('amount');
        $totalOutstanding= $query->clone()->where('authorization','approved')->whereIn('status',['Unpaid','Partially Paid'])->sum('balance');

        return view('livewire.salary-advances.index', compact('advances', 'totalGranted', 'totalOutstanding'));
    }
}
