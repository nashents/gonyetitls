<?php

namespace App\Http\Livewire\Leaves;

use App\Http\Livewire\Concerns\HasStayOnPageAuthorization;
use App\Mail\LeaveApplicationMail;
use App\Models\DepartmentHead;
use App\Models\EmployeeLeave;
use App\Models\Leave;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

class Pending extends Component
{
    use WithPagination;
    use HasStayOnPageAuthorization;

    protected $paginationTheme = 'bootstrap';
    public $search;
    public $range_from;
    public $range_to;

    private $leaves;
    public $leave_id;
    public $decision;
    public $reason;
    public $rank_names;
    public $role_names;
    public $department_names;
    public $category;
    public $hod;
    public $company;

    public $selectedRows = [];
    public $selectPageRows = false;

    public function updated($value){
        if (in_array($value, ['decision', 'reason'])) {
            $this->validateOnly($value);
        }
    }
    protected $rules = [
        'decision' => 'required|in:approved,rejected',
    ];
    public function mount(){
        $this->hod = DepartmentHead::where('employee_id', Auth::user()->employee->id)->first();
        $this->company = Auth::user()->employee->company;

        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
        $this->rank_names[] = $rank->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
        $this->role_names[] = $role->name;
        }
        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
        $this->department_names[] = $department->name;
        }
    }

    /** Super Admin, or Admin/Management in HR — authorizes at HR (management) level. */
    private function canAuthorizeAsHr(): bool
    {
        $inHrDept = in_array('Human Resources', $this->department_names ?? []);

        return in_array('Super Admin', $this->role_names ?? [])
            || (in_array('Admin', $this->role_names ?? []) && $inHrDept)
            || (in_array('Management', $this->rank_names ?? []) && $inHrDept);
    }

    /** The leaves this user may see/authorize on the Pending list. */
    private function pendingQuery()
    {
        // Drivers-only HR users (App\Support\HrAccess) see/authorize driver leave only
        $query = Leave::query()->visibleTo();

        if ($this->canAuthorizeAsHr()) {
            $query->where('management_decision', 'pending');
        } elseif (isset($this->hod)) {
            $query->where('hod_decision', 'pending')
                ->where('department_id', $this->hod->department->id);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public function authorize($id, $category){
        $this->resetErrorBag();
        $this->reset(['decision', 'reason']);
        $this->category = $category;
        $this->leave_id = $id;
        $this->dispatchBrowserEvent('show-decisionModal');
    }

    public function updatedSelectPageRows($value){
        if ($value) {
            $this->selectedRows = $this->pendingQuery()
                ->where('employee_id', '!=', Auth::user()->employee->id)
                ->latest()
                ->paginate(10)
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->reset(['selectedRows', 'selectPageRows']);
        }
    }

    public function showBulkyAuthorize(){
        $this->resetErrorBag();
        $this->reset(['decision', 'reason']);
        $this->dispatchBrowserEvent('show-bulkyAuthorizationModal');
    }

    /**
     * Apply the current decision to one leave at the given level ('hr' or 'hod').
     * Approval at HR level deducts the leave days and emails the employee.
     */
    private function applyDecision(Leave $leave, string $category): void
    {
        DB::transaction(function () use ($leave, $category) {
            if ($category == "hr") {
                $leave->management_id = Auth::user()->id;
                $leave->management_decision = $this->decision;
                $leave->management_reply = $this->reason;
                if ($leave->hod_id == Null) {
                    $leave->hod_id = Auth::user()->id;
                    $leave->hod_decision = $this->decision;
                    $leave->hod_reply = $this->reason;
                }
                $leave->status = $this->decision;
                $leave->update();

                if ($this->decision == "approved") {
                    $employee = $leave->employee;
                    $leaveType = $leave->leave_type;

                    if ($employee && $leaveType) {
                        $employeeLeave = EmployeeLeave::where('employee_id', $employee->id)
                            ->where('leave_type_id', $leaveType->id)
                            ->first();

                        if ($employeeLeave && is_numeric($leave->days)) {
                            $employeeLeave->available_leave_days = max(
                                0,
                                ($employeeLeave->available_leave_days ?? 0) - $leave->days
                            );
                            $employeeLeave->save();
                        }

                        // Backward compatibility
                        if (strtolower(trim($leaveType->name)) === 'annual') {
                            $employee->leave_days = max(
                                0,
                                ($employee->leave_days ?? 0) - $leave->days
                            );
                            $employee->save();
                        }
                    }
                }
            } elseif ($category == "hod") {
                $leave->hod_id = Auth::user()->id;
                $leave->hod_decision = $this->decision;
                $leave->hod_reply = $this->reason;
                $leave->update();
            }
        });

        // Email outside the transaction; a mail failure must not undo the decision.
        if ($category == "hr" && $this->decision == "approved") {
            $employee = $leave->employee;
            if ($employee && $employee->personal_email) {
                try {
                    Mail::to($employee->personal_email)
                        ->send(new LeaveApplicationMail($this->company, $leave));
                } catch (\Throwable $e) {
                    Log::warning('Leave approval email failed', ['leave_id' => $leave->id, 'error' => $e->getMessage()]);
                }
            }
        }
    }

    public function decision(){
        $this->validate();

        $leave = Leave::find($this->leave_id);
        if (!$leave) {
            $this->dispatchBrowserEvent('hide-decisionModal');
            return;
        }
        \App\Support\HrAccess::authorizeEmployee($leave->employee_id);

        $this->applyDecision($leave, $this->category);
        $decision = $this->decision;

        $this->dispatchBrowserEvent('hide-decisionModal');
        $this->reset(['decision', 'reason', 'leave_id', 'category']);
        $this->selectedRows = array_values(array_diff((array) $this->selectedRows, [(string) $leave->id]));

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=> $decision == "approved" ? "Leave Application Approved Successfully!!" : "Application Rejected Successfully!!"
        ]);

        return $this->redirectOrStay($decision == "approved" ? 'leaves.approved' : 'leaves.rejected');
    }

    public function authorizeSelectedRows(){
        $this->validate();

        if ($this->canAuthorizeAsHr()) {
            $category = 'hr';
        } elseif (isset($this->hod)) {
            $category = 'hod';
        } else {
            $this->dispatchBrowserEvent('hide-bulkyAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"You are not allowed to authorize leave applications."
            ]);
            return;
        }

        // Only leaves still on this user's pending list, never their own.
        $leaves = $this->pendingQuery()
            ->whereIn('id', (array) $this->selectedRows)
            ->where('employee_id', '!=', Auth::user()->employee->id)
            ->get();

        $done = 0;
        $failed = 0;
        foreach ($leaves as $leave) {
            try {
                $this->applyDecision($leave, $category);
                $done++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Bulk leave authorization failed', ['leave_id' => $leave->id, 'error' => $e->getMessage()]);
            }
        }
        $skipped = count((array) $this->selectedRows) - $leaves->count();
        $decision = $this->decision;

        $this->reset(['selectedRows', 'selectPageRows', 'decision', 'reason']);
        $this->dispatchBrowserEvent('hide-bulkyAuthorizationModal');

        $message = $done . " Leave Application(s) " . ($decision == "approved" ? "Approved" : "Rejected") . " Successfully!!";
        if ($skipped > 0) {
            $message .= " " . $skipped . " skipped (own application or no longer pending).";
        }
        if ($failed > 0) {
            $message .= " " . $failed . " failed, see log.";
        }
        $this->dispatchBrowserEvent('alert',[
            'type'=> $failed > 0 ? 'warning' : 'success',
            'message'=> $message,
        ]);

        if ($done === 0) {
            return;
        }

        return $this->redirectOrStay($decision == "approved" ? 'leaves.approved' : 'leaves.rejected');
    }

    public function render()
    {
        return view('livewire.leaves.pending', [
            'leaves' => $this->pendingQuery()->latest()->paginate(10),
        ]);
    }
}
