<?php

namespace App\Http\Livewire\Reports\PurchaseByVendors;

use Carbon\Carbon;
use App\Models\Vendor;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\ReportFormatter;
use App\Services\VendorPurchaseCalculator;

class Index extends Component
{
    const ACCRUAL = 'Accrual (Paid & Unpaid)';
    const CASH_BASIS = 'Cash Basis';
    const TOP_N = 10;

    public $from;
    public $to;
    public $selectedType = self::ACCRUAL;
    public $details;
    public $summary = "summary";

    public $default_currency;
    public $default_currency_id;

    public $vendor_items = [];
    public $total_purchases = 0;

    public function mount()
    {
        $this->to = Carbon::today()->format('Y-m-d');
        $this->from = Carbon::now()->firstOfYear()->format('Y-m-d');
    }

    public function set_report($value)
    {
        $this->summary = null;
        $this->details = null;

        if ($value == "details") {
            $this->details = 'details';
        } elseif ($value == "summary") {
            $this->summary = 'summary';
        }
    }

    /**
     * Bound to the filter form's wire:submit so pressing Enter doesn't
     * error out looking for a missing action; the bound properties
     * already trigger a recalculation on change.
     */
    public function generateStatement()
    {
        //
    }

    public function viewMode(): string
    {
        return $this->details ? 'details' : 'summary';
    }

    public function fmt($value): string
    {
        return ReportFormatter::money($value);
    }

    public function render()
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;
        $this->default_currency = $company?->currency;
        $this->default_currency_id = $company?->currency_id;

        if (isset($this->from) && isset($this->to)) {
            $calculator = new VendorPurchaseCalculator(
                $this->from,
                $this->to,
                $this->selectedType === self::CASH_BASIS,
                $this->default_currency_id
            );

            [$this->total_purchases, $purchasesByVendor] = $calculator->purchasesByVendor();

            $names = Vendor::whereIn('id', array_keys($purchasesByVendor))->pluck('name', 'id')->all();

            $this->vendor_items = self::buildItems($purchasesByVendor, $names, !$this->details);
        }

        return view('livewire.reports.purchase-by-vendors.index');
    }

    /**
     * Rows sorted by spend descending. In summary mode, only the top 10
     * vendors are itemized and the rest are folded into a single "All
     * Other Vendors" line, rather than a long tail of small amounts.
     */
    public static function buildItems(array $byVendor, array $names, bool $topTenOnly): array
    {
        $rows = [];

        foreach ($byVendor as $vendorId => $amount) {
            if ($amount == 0) {
                continue;
            }

            $rows[] = ['label' => $names[$vendorId] ?? 'Uncategorized Vendor', 'amount' => $amount];
        }

        usort($rows, fn ($a, $b) => $b['amount'] <=> $a['amount']);

        if ($topTenOnly && count($rows) > self::TOP_N) {
            $top = array_slice($rows, 0, self::TOP_N);
            $otherTotal = array_sum(array_column(array_slice($rows, self::TOP_N), 'amount'));
            $top[] = ['label' => 'All Other Vendors', 'amount' => $otherTotal];

            return $top;
        }

        return $rows;
    }
}
