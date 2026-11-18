<?php

namespace App\Support;

use App\Models\Company;
use App\Models\Department;
use App\Models\DepartmentHead;
use App\Models\User;
use App\Services\EditAuthorizationService;
use App\Services\Integrations\IntegrationGate;

/**
 * The flags/lists that module_groups / modules / sub_modules "visibility" JSON is
 * evaluated against (App\Support\Menu::isVisible), built for ANY user — the
 * sidebar (SidebarComposer), the route guard and the Module Access screen all
 * use this, so they agree on what a user can see.
 */
class MenuContext
{
    /** Per-request memo by user id. */
    private static array $memo = [];

    public static function build(User $user): array
    {
        if (isset(static::$memo[$user->id])) {
            return static::$memo[$user->id];
        }

        $employee = $user->employee;

        $department_names = $employee ? $employee->departments->pluck('name')->all() : [];
        $role_names = $user->roles->pluck('name')->all();
        $rank_names = $employee ? $employee->ranks->pluck('name')->all() : [];

        $deptIds = Department::whereIn('name', ['HSEQ', 'Workshop', 'Finance', 'Human Resources', 'Transport & Logistics', 'Stores'])
            ->pluck('id', 'name');

        $headOf = $employee
            ? DepartmentHead::where('employee_id', $employee->id)->pluck('department_id')->map(fn ($i) => (int) $i)->all()
            : [];
        $isHeadOf = fn (string $name) => isset($deptIds[$name]) && in_array((int) $deptIds[$name], $headOf, true);

        $isManagement = in_array('Management', $rank_names);
        $isSuperAdmin = in_array('Super Admin', $role_names);
        $isAdmin = in_array('Admin', $role_names);
        $inTransport = in_array('Transport & Logistics', $department_names);

        $company = $employee?->company ?? $user->company ?? $user->transporter?->company ?? $user->customer?->company ?? $user->agent?->company;
        $companyId = $employee?->company_id ?? $user->company_id ?? $user->company?->id;

        $admin_company = Company::where('type', 'admin')->first();

        return static::$memo[$user->id] = [
            'isAdmin'          => $isAdmin,
            'isSuperAdmin'     => $isSuperAdmin,
            'isEditAuthorizer' => app(EditAuthorizationService::class)->isAuthorizer($user),
            'isHOD'            => !empty($headOf),
            'isUser'           => in_array('User', $role_names),
            'isManagement'     => $isManagement,
            'isEmployee'       => in_array('Employee', $rank_names),
            'isDirector'       => in_array('Directors', $rank_names),
            'isDriver'         => (bool) ($user->driver ?? false),
            'isSystemAdmin'    => (bool) $user->is_admin(),
            'in_transport_admin'      => $isAdmin && $inTransport,
            'in_transport_management' => $isManagement && $inTransport,
            'inHR'          => in_array('Human Resources', $department_names),
            'inFinance'     => in_array('Finance', $department_names),
            'inTransport'   => $inTransport,
            'inHSEQ'        => in_array('HSEQ', $department_names),
            'inSecurity'    => in_array('Security', $department_names),
            'inStores'      => in_array('Stores', $department_names),
            'inWorkshop'    => in_array('Workshop', $department_names),
            'inFreight'     => in_array('Freight Forwarding & Clearing', $department_names),
            'companyIsFreightForwarder' => (bool) ($company?->hasCompanyType('Freight Forwarding & Clearing') ?? false),
            'isNotDriver'   => !($employee?->driver),
            'hasTrackingIntegration'   => IntegrationGate::activeForCompanyType('tracking', $companyId),
            'hasEzyTrackIntegration'   => IntegrationGate::activeForCompany('ezytrack', $companyId),
            'hasFanTrackerIntegration' => IntegrationGate::activeForCompany('fantracker', $companyId),
            'hasPinpointIntegration'   => IntegrationGate::activeForCompany('pinpoint', $companyId),
            'hasStoresDeptHead'   => $isHeadOf('Stores'),
            'hasWorkshopDeptHead' => $isHeadOf('Workshop'),
            'hasHRDeptHead'       => $isHeadOf('Human Resources'),
            'hasDeptHead'         => !empty($headOf),
            'hasHSEQDeptHead'     => $isHeadOf('HSEQ'),
            'hasTLDeptHead'       => $isHeadOf('Transport & Logistics'),
            'hasFinanceDeptHead'  => $isHeadOf('Finance'),
            'hasVehicleAssignment' => (bool) ($employee?->vehicleAssignments?->where('status', 1)->count() > 0),
            'admin_company_id' => $admin_company?->id,
            'driver_id'        => $employee?->driver?->id,
            'department_id'    => $employee?->departments->first()?->id,

            'roles'       => $role_names,
            'departments' => $department_names,
            'ranks'       => $rank_names,

            'user_id'      => $user->id,
            'employee_id'  => $employee?->id ?? $user->employee_id,
            'company_id'   => $employee?->company_id,
            'hseq_department_id' => $deptIds['HSEQ'] ?? null,
        ];
    }
}
