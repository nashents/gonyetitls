<?php

namespace App\Http\Livewire\Employees;

use App\Models\Employee;
use App\Models\ModuleGroup;
use App\Support\ModuleAccess as ModuleAccessSupport;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Per-user module locks (App\Support\ModuleAccess). Lock whole module groups
 * (e.g. Fleet Management, Fuel Management) or single modules for one user, on
 * top of their department access. is_admin / Super Admin only.
 */
class ModuleAccess extends Component
{
    public $employee_id;
    public $user_id;
    public $blockedGroups = [];
    public $blockedModules = [];

    public function mount($id)
    {
        $this->authorizeManager();

        $employee = Employee::with('user')->findOrFail($id);
        abort_unless($employee->user, 404, 'This employee has no user account.');

        $this->employee_id = $employee->id;
        $this->user_id = $employee->user->id;

        // Read rows directly: blocks() reports none for exempt users, but we show what's stored
        $rows = \App\Models\ModuleAccessBlock::where('user_id', $this->user_id)->get();
        $this->blockedGroups = $rows->pluck('module_group_id')->filter()->map(fn ($i) => (string) $i)->values()->all();
        $this->blockedModules = $rows->pluck('module_id')->filter()->map(fn ($i) => (string) $i)->values()->all();
    }

    private function authorizeManager(): void
    {
        $me = Auth::user();
        abort_unless($me && ($me->is_admin() || $me->isSuperAdmin()), 403, 'Only system admins and Super Admins can manage module access.');
    }

    public function save()
    {
        $this->authorizeManager();

        $employee = Employee::with('user')->findOrFail($this->employee_id);

        ModuleAccessSupport::sync($employee->user, (array) $this->blockedGroups, (array) $this->blockedModules);

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Module access saved for ' . $employee->name . ' ' . $employee->surname . '.',
        ]);
    }

    public function clearAll()
    {
        $this->blockedGroups = [];
        $this->blockedModules = [];
    }

    public function render()
    {
        $employee = Employee::with(['user', 'departments'])->find($this->employee_id);

        // Only what this user can currently see in the sidebar (department/role
        // visibility) — anything else is already blocked by the department guard.
        $vis = ModuleAccessSupport::visibility($employee->user);
        $exempt = ModuleAccessSupport::EXEMPT_MODULE_SLUGS;

        $groups = ModuleAccessSupport::tree()
            ->filter(fn ($g) => $vis['groups'][$g->id] ?? false)
            ->map(function ($g) use ($vis, $exempt) {
                $g = clone $g; // tree() is shared per request — don't filter it in place
                $g->setRelation('modules', $g->modules->filter(
                    fn ($m) => ($vis['modules'][$m->id] ?? false) && !in_array($m->slug, $exempt, true)
                )->values());
                return $g;
            })
            ->filter(fn ($g) => $g->modules->isNotEmpty())
            ->values();

        return view('livewire.employees.module-access', [
            'employee' => $employee,
            'groups' => $groups,
            'isExempt' => $employee->user ? ModuleAccessSupport::isExempt($employee->user) : false,
            'exemptSlugs' => ModuleAccessSupport::EXEMPT_MODULE_SLUGS,
        ]);
    }
}
