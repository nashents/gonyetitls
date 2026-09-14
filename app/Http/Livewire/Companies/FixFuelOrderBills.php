<?php

namespace App\Http\Livewire\Companies;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * One-off backfill for a bug in Fuels\Approved::update() — approving a "Once
 * Off Buy" fuel order attached to a trip created a Bill/BillExpense that never
 * set bills.authorization (stuck at the column default 'pending') or
 * bill_expenses.subtotal_incl/exchange_amount (stuck at null). Both P&L reports
 * require authorization = 'approved' and value each line off subtotal_incl /
 * exchange_amount, so every one of these bills was silently invisible there.
 * The bug itself is fixed in Fuels\Approved; this repairs bills it already
 * created before the fix.
 */
class FixFuelOrderBills extends Component
{
    public int $pendingAuthCount = 0;
    public int $pendingAmountCount = 0;
    public bool $done = false;
    public int $updatedAuthCount = 0;
    public int $updatedAmountCount = 0;

    public function mount()
    {
        abort_unless(Auth::user()->is_admin(), 403);

        $this->loadPreview();
    }

    public function loadPreview()
    {
        $this->done = false;
        $this->pendingAuthCount   = $this->staleAuthorizationQuery()->count();
        $this->pendingAmountCount = $this->staleAmountQuery()->count();
    }

    public function run()
    {
        abort_unless(Auth::user()->is_admin(), 403);

        $this->updatedAuthCount = $this->staleAuthorizationQuery()->update([
            'authorization' => 'approved',
        ]);

        $this->updatedAmountCount = $this->staleAmountQuery()->update([
            'bill_expenses.subtotal_incl'   => DB::raw('bill_expenses.subtotal'),
            'bill_expenses.exchange_amount' => DB::raw('fuels.exchange_amount'),
        ]);

        $this->done = true;

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Fixed {$this->updatedAuthCount} bill(s) stuck pending and {$this->updatedAmountCount} bill expense line(s) missing an amount.",
        ]);

        $this->loadPreview();
    }

    /**
     * Bug-signature bills (fuel-order bill, never marked approved) still stuck
     * at the 'pending' default.
     */
    protected function staleAuthorizationQuery()
    {
        return DB::table('bills')
            ->whereNull('deleted_at')
            ->where('category', 'Trip Expense')
            ->whereNotNull('fuel_id')
            ->where('authorization', '!=', 'approved');
    }

    /**
     * Bill expense lines on those same bug-signature bills that never got a
     * subtotal_incl / exchange_amount, so the P&L reports value them at 0.
     */
    protected function staleAmountQuery()
    {
        return DB::table('bill_expenses')
            ->join('bills', 'bills.id', '=', 'bill_expenses.bill_id')
            ->join('fuels', 'fuels.id', '=', 'bills.fuel_id')
            ->whereNull('bill_expenses.deleted_at')
            ->whereNull('bills.deleted_at')
            ->whereNull('fuels.deleted_at')
            ->where('bills.category', 'Trip Expense')
            ->whereNotNull('bills.fuel_id')
            ->where(function ($query) {
                $query->whereNull('bill_expenses.subtotal_incl')
                    ->orWhereNull('bill_expenses.exchange_amount');
            });
    }

    public function render()
    {
        return view('livewire.companies.fix-fuel-order-bills');
    }
}
