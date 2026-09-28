<?php

use App\Models\Bill;
use App\Models\User;
use App\Services\Accounting\FuelJournalService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * TopUps/Pending's single-row approval used to build its own top-up Bill
 * ("Fuel Station Fuel Topup") booked to Fuel - Ops - expensing Bulk Buy fuel
 * at purchase, then again as Fuel - COGS/Ops when drawn from the tank, while
 * Fuel Inventory went negative. Runs each through FuelJournalService::
 * postTopUp(), which moves the line to Fuel Inventory and reposts
 * (DR Fuel Inventory / CR Accounts Payable).
 *
 * Idempotent: a repaired bill is recategorised "Fuel Topup", so it isn't
 * picked up again.
 */
class RepostFuelStationTopUpBills extends Migration
{
    public function up()
    {
        $originalUser = Auth::user();
        $fixed = 0;

        $bills = Bill::where('category', 'Fuel Station Fuel Topup')->whereNotNull('top_up_id')->with('top_up')->get();

        foreach ($bills as $bill) {
            $topUp = $bill->top_up;
            if (! $topUp || $topUp->authorization !== 'approved') {
                echo "  skipped {$bill->bill_number}: top-up missing or not approved\n";
                continue;
            }

            // Bills carry no company_id - posting uses the acting user's company.
            if ($user = User::find($bill->user_id ?: $topUp->user_id)) {
                Auth::setUser($user);
            }

            try {
                app(FuelJournalService::class)->postTopUp($topUp);
                $fixed++;
            } catch (\Throwable $e) {
                echo "  failed {$bill->bill_number}: {$e->getMessage()}\n";
                Log::error("RepostFuelStationTopUpBills: {$bill->bill_number}: " . $e->getMessage());
            }
        }

        if ($originalUser) {
            Auth::setUser($originalUser);
        }

        echo "  Reposted {$fixed} of {$bills->count()} top-up bill(s).\n";
    }

    public function down()
    {
        // Data repair - nothing to roll back.
    }
}
