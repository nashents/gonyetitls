<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Fuel;
use App\Services\Accounting\FuelFundingReversalService;

/**
 * Funded By: Company -> Customer on a fuel order whose supplier bill is
 * already settled (Suppliers Journal debit / supplier payment). The save is
 * held back and the user is shown what will be reversed (panel in
 * livewire.fuels._customer_supplied); confirming re-runs update() and the
 * settlement is reversed just before the fuel is re-posted.
 *
 * The component's save method must be update().
 */
trait ConfirmsFuelFundingReversal
{
    /** The reversal plan awaiting confirmation (FuelFundingReversalService::plan), or null. */
    public $fundingReversal = null;

    public $fundingReversalConfirmed = false;

    /**
     * Call before anything is saved. True = stop: the confirmation panel is
     * now showing (or the settlement can't be reversed from here).
     */
    protected function fundingReversalNeedsConfirmation(?Fuel $fuel, bool $toCustomer): bool
    {
        $plan = $fuel && $toCustomer ? app(FuelFundingReversalService::class)->plan($fuel) : null;

        if (!$plan || (empty($plan['items']) && !$plan['blocked'])) {
            $this->fundingReversal = null;
            return false;
        }

        if ($this->fundingReversalConfirmed && !$plan['blocked']) {
            return false;
        }

        $this->fundingReversal = $plan;
        $this->dispatchBrowserEvent('alert', [
            'type'    => 'warning',
            'message' => $plan['blocked'] ?: "This fuel's supplier bill is already settled - confirm the reversal shown under Funded By.",
        ]);

        return true;
    }

    /** Call right before the fuel order is re-posted (inside the save's transaction). */
    protected function applyFundingReversal(Fuel $fuel, bool $toCustomer): void
    {
        if ($toCustomer && $this->fundingReversalConfirmed) {
            app(FuelFundingReversalService::class)->reverse($fuel);
        }

        $this->fundingReversal = null;
    }

    public function confirmFundingReversal()
    {
        $this->fundingReversalConfirmed = true;

        try {
            return $this->update();
        } finally {
            $this->fundingReversalConfirmed = false;
        }
    }

    public function cancelFundingReversal()
    {
        $this->fundingReversal = null;
        $this->fundingReversalConfirmed = false;
    }
}
