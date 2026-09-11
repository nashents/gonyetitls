<?php

namespace App\Http\Livewire\Companies;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FixTripExpenseDates extends Component
{
    public int $pendingCount = 0;
    public int $pendingBillCount = 0;
    public bool $done = false;
    public int $updatedCount = 0;
    public int $updatedBillCount = 0;

    public function mount()
    {
        abort_unless(Auth::user()->is_admin(), 403);

        $this->loadPreview();
    }

    public function loadPreview()
    {
        $this->done = false;
        $this->pendingCount = $this->outOfSyncQuery()->count();
        $this->pendingBillCount = $this->outOfSyncBillsQuery()->count();
    }

    public function run()
    {
        abort_unless(Auth::user()->is_admin(), 403);

        // Bills first — they join through trip_expenses.date, so once that's
        // rewritten below the comparison would no longer find them.
        $this->updatedBillCount = $this->outOfSyncBillsQuery()->update([
            'bills.bill_date' => DB::raw('trips.start_date'),
        ]);

        $this->updatedCount = $this->outOfSyncQuery()->update([
            'trip_expenses.date' => DB::raw('trips.start_date'),
        ]);

        $this->done = true;

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Updated {$this->updatedCount} trip expense(s) and {$this->updatedBillCount} linked bill(s) to match their trip's start date.",
        ]);

        $this->loadPreview();
    }

    /**
     * Trip expenses whose date doesn't match their (non-deleted) trip's start date.
     * A plain DB update/count — never hydrated into Eloquent models — since this
     * can touch every trip expense in the system.
     */
    protected function outOfSyncQuery()
    {
        return DB::table('trip_expenses')
            ->join('trips', 'trips.id', '=', 'trip_expenses.trip_id')
            ->whereNull('trip_expenses.deleted_at')
            ->whereNull('trips.deleted_at')
            ->whereNotNull('trips.start_date')
            ->where(function ($query) {
                $query->whereColumn('trip_expenses.date', '!=', 'trips.start_date')
                    ->orWhereNull('trip_expenses.date');
            });
    }

    /**
     * Bills linked to a trip expense (via bills.trip_expense_id) whose bill_date
     * doesn't match the trip's start date.
     */
    protected function outOfSyncBillsQuery()
    {
        return DB::table('bills')
            ->join('trip_expenses', 'trip_expenses.id', '=', 'bills.trip_expense_id')
            ->join('trips', 'trips.id', '=', 'trip_expenses.trip_id')
            ->whereNull('bills.deleted_at')
            ->whereNull('trip_expenses.deleted_at')
            ->whereNull('trips.deleted_at')
            ->whereNotNull('trips.start_date')
            ->where(function ($query) {
                $query->whereColumn('bills.bill_date', '!=', 'trips.start_date')
                    ->orWhereNull('bills.bill_date');
            });
    }

    public function render()
    {
        return view('livewire.companies.fix-trip-expense-dates');
    }
}
