<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Company;
use App\Models\Trip;
use App\Models\TripExpense;
use Illuminate\Support\Collection;

/**
 * Trip expenses that are not company costs (single source of truth for every
 * screen and posting):
 *
 *  - Category "Transporter", paid by the company: the bill debits
 *    Transporter Recoverable (not an expense). Recovered per the company's
 *    Business Setting `transporter_expense_recovery`:
 *      'deduct'  - a deduction line on the trip's transporter payment bill
 *                  (TransporterPaymentBillService), crediting the recoverable;
 *      'invoice' - an "Invoice to: Transporter", whose lines credit the
 *                  recoverable instead of revenue.
 *  - Category "Transporter", Funded By: Transporter (their own driver paid):
 *    recorded on the trip only - no bill, no ledger, no deduction.
 *  - Category "Transporter", Funded By: Customer (the customer paid the
 *    supplier as part-payment, but the cost is the transporter's): DR
 *    Transporter Recoverable / CR Accounts Receivable (CustomerFuelSupply),
 *    recovered from the transporter exactly like a company-paid line.
 *  - Category "Customer", paid by the company: the bill debits Customer
 *    Recoverable; the customer invoice's recharge portion
 *    (invoice_items.recharge_amount) credits it instead of Sales.
 *
 * Fuel-order (fuel_id) and transporter-payment (transporter_id) rows have
 * their own flows and are never treated as recoverables here.
 */
class TripExpenseRecoveryService
{
    public const TRANSPORTER_RECOVERABLE = 'Transporter Recoverable';
    public const CUSTOMER_RECOVERABLE = 'Customer Recoverable';

    public function transporterRecoverable(): Account
    {
        return Account::where('name', self::TRANSPORTER_RECOVERABLE)->firstOrFail();
    }

    public function customerRecoverable(): Account
    {
        return Account::where('name', self::CUSTOMER_RECOVERABLE)->firstOrFail();
    }

    /** 'deduct' (default) or 'invoice'. */
    public function transporterRecoveryMode(?Company $company): string
    {
        return ($company?->transporter_expense_recovery ?? 'deduct') === 'invoice' ? 'invoice' : 'deduct';
    }

    /** Recorded on the trip only - the transporter's own driver paid. */
    public function isTransporterFunded(TripExpense $tripExpense): bool
    {
        return $tripExpense->category === 'Transporter' && (bool) $tripExpense->funded_by_transporter;
    }

    /** Recoverable account for a company-paid Transporter/Customer line, else null (a company cost). */
    public function recoverableAccountFor(TripExpense $tripExpense): ?Account
    {
        if ($tripExpense->fuel_id || $tripExpense->transporter_id
            || $tripExpense->supplied_by_customer || $tripExpense->funded_by_transporter) {
            return null;
        }

        return match ($tripExpense->category) {
            'Transporter' => $this->transporterRecoverable(),
            'Customer' => $this->customerRecoverable(),
            default => null,
        };
    }

    /**
     * Debit account for a customer-funded line (CustomerFuelSupplyService):
     * Transporter Recoverable when the cost is the transporter's, else null
     * (the expense's own account, as before).
     */
    public function customerFundedDebitAccount(TripExpense $tripExpense): ?Account
    {
        return $tripExpense->category === 'Transporter' && !$tripExpense->fuel_id && !$tripExpense->transporter_id
            ? $this->transporterRecoverable()
            : null;
    }

    /**
     * Transporter-category lines of a trip that posted to Transporter
     * Recoverable - company paid (billable) or customer funded - i.e. what
     * the transporter owes back.
     */
    public function transporterDeductions(Trip $trip): Collection
    {
        $journal = app(TripExpenseJournalService::class);

        return $trip->trip_expenses()->with('expense', 'allowance', 'fuel')->get()
            ->filter(function (TripExpense $e) use ($journal) {
                if ($e->category !== 'Transporter' || $e->transporter_id || $e->funded_by_transporter || (float) $e->amount <= 0) {
                    return false;
                }
                // Fuel issued to the subcontracted truck: an approved fuel order
                // posted to Transporter Recoverable (FuelJournalService).
                if ($e->fuel_id) {
                    return $e->fuel && $e->fuel->authorization === 'approved' && $this->isTransporterFuel($e->fuel);
                }

                return $e->supplied_by_customer || $journal->isBillable($e);
            })
            ->values();
    }

    /** A trip fuel order the transporter must pay back (not customer supplied). */
    public function isTransporterFuel(\App\Models\Fuel $fuel): bool
    {
        if (!$fuel->trip_id || app(CustomerFuelSupplyService::class)->appliesToFuel($fuel)) {
            return false;
        }

        return $fuel->category === 'Transporter'
            || optional($fuel->trip_expense)->category === 'Transporter';
    }

    /**
     * Stamp a transporter fuel order with what the transporter is charged
     * (Business Settings: at cost or marked up) and return the markup amount.
     */
    public function applyFuelMarkup(\App\Models\Fuel $fuel, ?Company $company, ?float $allocatedCost = null): float
    {
        $pct = ($company?->transporter_fuel_recovery === 'markup') ? max(0, (float) $company->transporter_fuel_markup) : 0.0;
        // Only the litres allocated to trips are recovered (and marked up).
        $cost = round($allocatedCost ?? (float) $fuel->amount, 2);
        $markup = round($cost * $pct / 100, 2);

        $fuel->transporter_price = round((float) $fuel->unit_price * (1 + $pct / 100), 4);
        $fuel->transporter_total = $cost + $markup;
        $fuel->profit = $markup;
        $fuel->saveQuietly();

        return $markup;
    }

    /**
     * What the transporter owes for this line: its amount, plus its share of
     * the fuel markup when it is one trip's allocation of a fuel order.
     */
    public function recoveryAmount(TripExpense $tripExpense): float
    {
        $fuel = $tripExpense->fuel_id ? $tripExpense->fuel : null;
        $total = (float) optional($fuel)->transporter_total;
        if ($total <= 0) {
            return (float) $tripExpense->amount;
        }

        // transporter_total = allocated cost + markup; scale this trip's share.
        $cost = $total - (float) $fuel->profit;

        return $cost > 0 ? round((float) $tripExpense->amount * $total / $cost, 2) : (float) $tripExpense->amount;
    }

    /** Company-paid Customer-category lines of a trip in $currencyId - the recharge. */
    public function customerRecharges(Trip $trip, $currencyId): Collection
    {
        $journal = app(TripExpenseJournalService::class);

        return $trip->trip_expenses()->with('expense', 'allowance')->get()
            ->filter(fn (TripExpense $e) => $e->category === 'Customer'
                && (int) $e->currency_id === (int) $currencyId
                && $this->recoverableAccountFor($e) !== null
                && $journal->isBillable($e)
                && is_numeric($e->amount))
            ->values();
    }

    /**
     * Convert a trip expense amount into $currencyId via the company base
     * currency (exchange_amount holds the base value for foreign lines).
     */
    public function amountIn(TripExpense $tripExpense, $currencyId, ?Company $company, $targetRate = null): float
    {
        $amount = $this->recoveryAmount($tripExpense);
        if ((int) $tripExpense->currency_id === (int) $currencyId) {
            return $amount;
        }

        // Scale the stored base amount by any fuel markup.
        $scale = (float) $tripExpense->amount > 0 ? $amount / (float) $tripExpense->amount : 1;
        $baseId = $company?->currency_id;
        $base = (int) $tripExpense->currency_id === (int) $baseId
            ? $amount
            : (float) ($tripExpense->exchange_amount ? $tripExpense->exchange_amount * $scale : $amount * (float) ($tripExpense->exchange_rate ?: 1));

        if ((int) $currencyId === (int) $baseId) {
            return $base;
        }

        $rate = (float) $targetRate;
        return $rate > 0 ? $base / $rate : $base;
    }
}
