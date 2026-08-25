<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Gates the Payroll Config screen (proration/controls/GL account routing,
 * including the driver/admin COGS-Ops split). Matches the access rule
 * already used for other GL-adjacent settings pages in MenuRegistrySeeder
 * (isAdmin && inFinance, or isSuperAdmin) rather than the broader
 * HR-inclusive group PayrollRunPolicy uses for day-to-day payroll runs —
 * mapping GL accounts is a controller/finance decision, not an HR one.
 */
class PayrollCompanyConfigPolicy
{
    use HandlesAuthorization;

    private function isSuperAdmin(User $user): bool
    {
        return $user->roles->pluck('name')->contains('Super Admin');
    }

    private function isAdmin(User $user): bool
    {
        return $user->roles->pluck('name')->contains('Admin');
    }

    private function inFinance(User $user): bool
    {
        return $user->employee?->departments->pluck('name')->contains('Finance') ?? false;
    }

    public function view(User $user): bool
    {
        return $this->isSuperAdmin($user) || ($this->isAdmin($user) && $this->inFinance($user));
    }

    public function update(User $user): bool
    {
        return $this->view($user);
    }
}
