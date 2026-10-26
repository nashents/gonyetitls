<?php

namespace App\Http\Livewire\Concerns;

use App\Models\CustomerFuelSupply;
use Carbon\Carbon;

/**
 * Shared by the horse / transporter Profit & Loss components (Index +
 * Preview), which list cost line items in the reporting currency.
 *
 *  - reportingAmount(): a foreign-currency bill line's own exchange_amount
 *    is missing on older bills (21 of 110 on COMDELTIC) - those counted as
 *    0. Falls back to the line total at the bill's rate.
 *  - customerFuelItems(): customer-supplied fuel has no Bill - it posts
 *    DR Fuel - COGS / CR AR through a CustomerFuelSupply - so a horse's P&L
 *    showed the litres in "Total Fuel Usage" but none of their cost
 *    (AGB3978 Aug-2026: 3 of 4 approved orders missing).
 *  - currencyLabel(): "ZAR (R) 19,891.15" - the line's original amount, so
 *    the reporting-currency Amount column isn't read as rand.
 *
 * Expects $this->default_currency_id, $this->fromDt, $this->toDt.
 */
trait ProfitLossLineItems
{
    protected function reportingAmount($billExpense, $bill): float
    {
        $total = (float) ($billExpense->subtotal_incl ?: $billExpense->subtotal);

        if (! $bill || (int) $bill->currency_id === (int) $this->default_currency_id) {
            return $total;
        }

        if ((float) $billExpense->exchange_amount != 0.0) {
            return (float) $billExpense->exchange_amount;
        }

        $rate = (float) ($bill->exchange_rate ?: $billExpense->exchange_rate);

        return $rate > 0 ? round($total * $rate, 2) : 0.0;
    }

    /**
     * The Item column. Lines with no expense/product/inventory - fuel bills
     * (only bills.fuel_id/top_up_id), allowance lines, tyre/asset lines and
     * description-only lines - showed a bare "—"; fall back through those,
     * then the account name. Fallback relations are lazy-loaded (rare rows);
     * the bill must be selected with fuel_id + top_up_id.
     */
    protected function resolveBillExpenseName($bill_expense): string
    {
        $productName = fn ($product) => $product ? trim(($product->brand?->name ?? '') . ' ' . ($product->name ?? '')) : '';

        $name = $bill_expense->expense?->name
            ?: $productName($bill_expense->product)
            ?: $productName($bill_expense->inventory?->product)
            ?: ($bill_expense->tyre_id ? trim('Tyre ' . $productName($bill_expense->tyre?->product) . ' ' . ($bill_expense->tyre?->serial_number ?? '')) : '')
            ?: ($bill_expense->asset_id ? trim('Asset ' . $productName($bill_expense->asset?->product) . ' ' . ($bill_expense->asset?->asset_number ?? '')) : '')
            ?: ($bill_expense->allowance_id ? trim('Allowance ' . ($bill_expense->allowance?->name ?? '')) : '')
            ?: trim((string) $bill_expense->description);

        if ($name === '') {
            $bill = $bill_expense->bill;
            if ($bill?->fuel_id) {
                $name = trim('Fuel ' . ($bill->fuel?->order_number ?? ''));
            } elseif ($bill?->top_up_id) {
                $name = trim('Fuel top-up ' . ($bill->top_up?->order_number ?? ''));
            }
        }

        return $name ?: ($bill_expense->account?->name ?? '—');
    }

    protected function currencyLabel($currency, ?float $originalAmount = null): string
    {
        $label = trim(($currency?->name ?? '') . (! empty($currency?->symbol) ? ' (' . $currency->symbol . ')' : ''));

        if ($currency && (int) $currency->id !== (int) $this->default_currency_id && $originalAmount !== null) {
            $label .= ' ' . number_format($originalAmount, 2);
        }

        return $label;
    }

    /**
     * Header fuel figures for the horse: [orders, litres, uncosted order
     * numbers]. fuels.date is a string column ("2026-08-01" or
     * "2026-08-06T14:31"), so it's compared by calendar day - a bare
     * whereBetween against "Y-m-d 00:00:00" dropped orders dated on the
     * period's first day. "Uncosted" = approved but with neither a live Bill
     * nor a customer supply (e.g. its bill was deleted by hand), so its
     * litres are in the header but no cost is in the P&L.
     */
    protected function fuelStats(int $horseId): array
    {
        $fuels = \App\Models\Fuel::query()
            ->where('horse_id', $horseId)
            ->where('authorization', 'approved')
            ->whereRaw('LEFT(`date`, 10) BETWEEN ? AND ?', [
                Carbon::parse($this->fromDt)->toDateString(),
                Carbon::parse($this->toDt)->toDateString(),
            ])
            ->get(['id', 'order_number', 'quantity']);

        $billed = \App\Models\Bill::whereIn('fuel_id', $fuels->pluck('id'))->pluck('fuel_id')->all();
        $supplied = CustomerFuelSupply::whereIn('fuel_id', $fuels->pluck('id'))->pluck('fuel_id')->all();

        $uncosted = $fuels->reject(fn ($f) => in_array($f->id, $billed) || in_array($f->id, $supplied))
            ->pluck('order_number')->values()->all();

        return [$fuels->count(), (float) $fuels->sum('quantity'), $uncosted];
    }

    /**
     * Customer-supplied fuel for the horse, as P&L line items. Trip-linked
     * supplies are matched on the trip's start_date (like trip bills, so the
     * cost lands in the same period as the freight it earned).
     */
    protected function customerFuelItems(int $horseId, array $accountIds): array
    {
        if (empty($accountIds)) {
            return [];
        }

        $supplies = CustomerFuelSupply::query()
            // Supplies with a supplier bill (2026-10-23 on) are already a bill line.
            ->whereNull('bill_id')
            ->whereIn('account_id', $accountIds)
            ->where(function ($q) use ($horseId) {
                $q->whereHas('trip', fn ($t) => $t->where('horse_id', $horseId))
                  ->orWhere(fn ($x) => $x->whereNull('trip_id')->whereHas('fuel', fn ($f) => $f->where('horse_id', $horseId)));
            })
            ->where(function ($q) {
                $q->whereHas('trip', fn ($t) => $t->whereBetween('start_date', [$this->fromDt, $this->toDt]))
                  ->orWhere(fn ($x) => $x->whereNull('trip_id')->whereBetween('date', [$this->fromDt, $this->toDt]));
            })
            ->with(['account.account_type:id,name', 'currency:id,name,symbol', 'trip:id,trip_number', 'fuel:id,order_number', 'customer:id,name'])
            ->orderBy('date')
            ->get();

        $items = [];
        foreach ($supplies as $supply) {
            $foreign = (int) $supply->currency_id !== (int) $this->default_currency_id;
            $amount = $foreign ? round((float) $supply->amount * (float) $supply->exchange_rate, 2) : (float) $supply->amount;
            if (abs($amount) < 0.00001) {
                continue;
            }

            $items[] = [
                'date'              => $supply->date ? Carbon::parse($supply->date)->format('Y-m-d') : null,
                'bill_number'       => $supply->supply_number,
                'trip_ref'          => $supply->trip?->trip_number ?? '',
                'account_name'      => $supply->account?->name ?? '—',
                'account_type_name' => $supply->account?->account_type?->name ?? 'Uncategorized',
                'item_name'         => trim('Customer-supplied fuel ' . ($supply->fuel?->order_number ?? '')
                                        . ($supply->customer ? ' (' . $supply->customer->name . ')' : '')),
                'expense_currency'  => $this->currencyLabel($supply->currency, (float) $supply->amount),
                'amount'            => $amount,
            ];
        }

        return $items;
    }
}
