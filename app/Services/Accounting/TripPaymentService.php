<?php

namespace App\Services\Accounting;

use App\Models\Company;
use App\Models\Trip;
use App\Models\TripPayment;
use Illuminate\Support\Facades\Auth;

/**
 * Owns a trip's payment history (trip_payments). Every payment keeps its own
 * date, currency and rate; trips.amount_paid / exchange_amount_paid /
 * paid_at / payment_status are always recomputed from the rows.
 */
class TripPaymentService
{
    /**
     * @param float      $amount        received, in $currencyId
     * @param float|null $exchangeRate  1 payment currency = ? trip currency (ignored when the same)
     */
    public function record(Trip $trip, string $date, ?int $currencyId, float $amount, ?float $exchangeRate = null, array $extra = []): TripPayment
    {
        $currencyId = $currencyId ?: $trip->currency_id;
        $sameCurrency = ! $trip->currency_id || (int) $currencyId === (int) $trip->currency_id;
        $rate = $sameCurrency ? 1.0 : (float) $exchangeRate;
        $tripAmount = round($amount * $rate, 2);

        $payment = new TripPayment;
        $payment->trip_id = $trip->id;
        $payment->source = $extra['source'] ?? 'manual';
        $payment->invoice_payment_item_id = $extra['invoice_payment_item_id'] ?? null;
        $payment->payment_date = $date;
        $payment->currency_id = $currencyId;
        $payment->amount = round($amount, 2);
        $payment->exchange_rate = $rate;
        $payment->trip_amount = $tripAmount;
        $payment->exchange_amount = array_key_exists('exchange_amount', $extra)
            ? $extra['exchange_amount']
            : $this->toBase($trip, $currencyId, $amount, $tripAmount);
        $payment->notes = $extra['notes'] ?? null;
        $payment->user_id = $extra['user_id'] ?? Auth::id();
        $payment->save();

        $this->recalculate($trip);

        return $payment;
    }

    public function delete(TripPayment $payment): void
    {
        $trip = $payment->trip;
        $payment->delete();

        if ($trip) {
            $this->recalculate($trip);
        }
    }

    /** Rewrites the trip's paid totals and status from its payment rows. */
    public function recalculate(Trip $trip): void
    {
        $rows = TripPayment::where('trip_id', $trip->id)->orderBy('payment_date')->orderBy('id')->get();
        $paid = round($rows->sum('trip_amount'), 2);
        $last = $rows->last();

        if ($paid <= 0) {
            $trip->amount_paid = null;
            $trip->exchange_amount_paid = null;
            $trip->payment_status = null;
            $trip->paid_at = null;
            $trip->paid_by = null;
        } else {
            $trip->amount_paid = $paid;
            $trip->exchange_amount_paid = $rows->contains(fn ($r) => $r->exchange_amount === null)
                ? ($trip->exchange_rate ? round($paid * (float) $trip->exchange_rate, 2) : null)
                : round($rows->sum('exchange_amount'), 2);
            $trip->payment_status = $paid >= (float) $trip->freight - 0.01 ? 'Paid' : 'Partial';
            $trip->paid_at = $last->payment_date;
            $trip->paid_by = $last->user_id;
        }

        $trip->save();
    }

    public function outstanding(Trip $trip): float
    {
        return max(0, round((float) $trip->freight - (float) TripPayment::where('trip_id', $trip->id)->sum('trip_amount'), 2));
    }

    public function baseCurrencyId(Trip $trip): ?int
    {
        $company = $trip->company_id ? Company::find($trip->company_id) : null;

        return $company?->currency_id ?? Auth::user()?->employee?->company?->currency_id;
    }

    /** Reporting-currency value of a payment. */
    protected function toBase(Trip $trip, ?int $currencyId, float $amount, float $tripAmount): ?float
    {
        $base = $this->baseCurrencyId($trip);

        if ((int) $currencyId === (int) $base) {
            return round($amount, 2);
        }
        if (! $trip->currency_id || (int) $trip->currency_id === (int) $base) {
            return round($tripAmount, 2);
        }

        return is_numeric($trip->exchange_rate) && $trip->exchange_rate > 0
            ? round($tripAmount * (float) $trip->exchange_rate, 2)
            : null;
    }
}
