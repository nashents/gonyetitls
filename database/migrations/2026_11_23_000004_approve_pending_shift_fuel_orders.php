<?php

use App\Models\Fuel;
use App\Models\User;
use App\Services\Fuel\FuelApprovalService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Shift fuel orders are now approved on creation, like the shift's trips.
 * Approve the ones raised from shifts before that, with the normal approval
 * side-effects (vehicle balance/mileage, Bulk Buy stock, consumption bill).
 * No station emails - these are historical. Rejected/deleted orders untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        $service = app(FuelApprovalService::class);

        Fuel::whereNotNull('shift_id')
            ->where(fn ($q) => $q->where('authorization', 'pending')->orWhereNull('authorization'))
            ->orderBy('id')
            ->each(function (Fuel $fuel) use ($service) {
                // Bill user/company come from the logged-in user (CLI has none)
                $user = $fuel->user_id ? User::find($fuel->user_id) : null;
                if ($user) {
                    Auth::setUser($user);
                }

                DB::transaction(fn () => $service->approve(
                    $fuel,
                    $user?->id,
                    'Auto-approved shift fuel order' . ($fuel->shift ? ' (' . $fuel->shift->shift_number . ')' : ''),
                    false
                ));
            });
    }

    public function down(): void
    {
        // Not reversible: approval posted bills and moved balances.
    }
};
