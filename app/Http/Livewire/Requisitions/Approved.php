<?php

namespace App\Http\Livewire\Requisitions;

use App\Models\Requisition;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Approved extends Component
{


    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $requisition_filter;

    private $requisitions;
    public $requisition_id;
    public $trip_id;
    public $authorize;
    public $comments;
    public $requisition;
    public $company;

    public function mount(){
        $this->company = Auth::user()->employee->company;
        $this->requisition_filter = 'created_at';
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

     public function findUser($id){
        $user = User::find($id);
        $name = $user?->name;
        $surname = $user?->surname;
        return $name ." ". $surname;
    }

    public function render()
    {
  
           // ✅ Force an Eloquent user model instance
        $user = User::query()
            ->with(['employee.departments', 'roles', 'employee.ranks'])
            ->findOrFail(Auth::id());

        $employee        = $user->employee;

        $departmentNames = $employee?->departments?->pluck('name')->all() ?? [];
        $roleNames       = $user->roles->pluck('name')->all();

        $isFinanceOrSuper = in_array('Finance', $departmentNames, true)
            || in_array('Super Admin', $roleNames, true);

        $base = Requisition::query()
            ->with(['employee', 'department', 'trip', 'currency', 'payments'])
            ->where('authorization','approved')
            ->orderBy($this->requisition_filter, 'desc');

        // Non-finance/non-super: restrict to their departments
        if (! $isFinanceOrSuper) {
            $base->whereIn('department_id', (array) $this->department_ids);
        }

        $canViewPaymentRequisitions =
        (
            in_array('Finance', $departmentNames)
            && in_array('Admin', $roleNames)
        )
        || in_array('Super Admin', $roleNames);

        if (!$canViewPaymentRequisitions) {
            $base->where('type', '!=', 'payment_requisition');
        }


        // 1. Date range / default period
        if (!empty($this->from) && !empty($this->to)) {
            // Use the selected column in $this->requisition_filter
            $base->whereBetween($this->requisition_filter, [$this->from, $this->to]);
        } else {
            // Default to current month & year on created_at
            $base->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }
        // Search (GROUPED OR CONDITIONS) — critical fix
        if (filled($this->search)) {
            $term = trim($this->search);

            $base->where(function ($q) use ($term) {
                $like = "%{$term}%";

                $q->where('requisition_number', 'like', $like)
                ->orWhere('subject', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('status', 'like', $like)
                ->orWhere('date', 'like', $like)
                ->orWhere('total', 'like', $like)

                ->orWhereHas('requisition_items.expense', function ($qq) use ($like) {
                    $qq->where('name', 'like', $like);
                })

                ->orWhereHas('trip', function ($qq) use ($like) {
                    $qq->where('trip_number', 'like', $like)
                        ->orWhereHas('horse', function ($hhh) use ($like) {
                            $hhh->where('registration_number', 'like', $like);
                        });
                })

                ->orWhereHas('employee', function ($qq) use ($term) {
                    $qq->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%{$term}%");
                })

                ->orWhereHas('currency', function ($qq) use ($like) {
                    $qq->where('name', 'like', $like);
                });
            });
        }

        return view('livewire.requisitions.approved', [
            'requisitions'        => $base->paginate(10),
            'requisition_filter'  => $this->requisition_filter,
        ]);
       
    }
}
