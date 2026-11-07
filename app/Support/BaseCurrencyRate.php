<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * A record in the company's own (reporting) currency always carries an
 * exchange rate of 1.
 *
 * Forms across the app fill the rate in when a foreign currency is picked
 * (ZAR, ZWG, ...) and only HIDE the rate field when the user switches back
 * to the reporting currency - the old rate stayed in the component and was
 * saved. On an invoice that booked Sales/AR at amount x the foreign rate and
 * the payment then cleared it as a huge "Realized FX loss" (TPI00007:
 * 2,100 USD at 16.2 -> 31,920 loss). Twenty-odd forms resolve rates their own
 * way, so this is enforced once, at save time, for every model.
 *
 * Runs on every Eloquent `saving` event. When the record's currency is its
 * company's reporting currency and its rate is not 1, the rate is set to 1
 * and every exchange_* amount column is brought back to the native figure
 * (it was native x old rate). The paid_currency_id / paid_exchange_rate pair
 * (cross-currency payments) gets the same treatment.
 *
 * Only on create, or when the currency or rate is itself being changed in
 * this save - an unrelated update (status, approval) of an old record must
 * not silently move its figures away from what is already in the ledger.
 * Old records are repaired by migration, which reposts the ledger too.
 */
class BaseCurrencyRate
{
    /** [currency column => [rate column, amount-column prefix]] */
    private const PAIRS = [
        'currency_id'      => ['exchange_rate', 'exchange_'],
        'paid_currency_id' => ['paid_exchange_rate', null],
    ];

    /**
     * Ledger lines carry what their posting service decided; exchange_rates
     * is the rate table itself.
     */
    private const SKIP_MODELS = [
        \App\Models\JournalEntryLine::class,
        \App\Models\ExchangeRate::class,
    ];

    /** company_id => reporting currency_id, per request */
    private static ?array $bases = null;

    public static function handle(string $event, array $payload): void
    {
        $model = $payload[0] ?? null;

        if (! $model instanceof Model || in_array(get_class($model), self::SKIP_MODELS, true)) {
            return;
        }

        foreach (self::PAIRS as $currencyColumn => [$rateColumn, $amountPrefix]) {
            if (! array_key_exists($rateColumn, $model->getAttributes())) {
                continue;
            }

            if ($model->exists && ! $model->isDirty([$currencyColumn, $rateColumn])) {
                continue;
            }

            $currencyId = $model->getAttribute($currencyColumn);
            $rate = $model->getAttribute($rateColumn);

            if (! $currencyId || ! is_numeric($rate) || abs((float) $rate - 1) < 0.000001) {
                continue;
            }

            $base = self::baseCurrencyFor($model);
            if (! $base || (int) $currencyId !== (int) $base) {
                continue;
            }

            $model->setAttribute($rateColumn, 1);

            if ($amountPrefix && (float) $rate > 0) {
                $attributes = $model->getAttributes();
                foreach ($attributes as $column => $value) {
                    if ($column === $rateColumn || ! str_starts_with($column, $amountPrefix) || ! is_numeric($value)) {
                        continue;
                    }

                    // At rate 1 the reporting figure IS the native one: copy
                    // it (exchange_amount <- amount, document headers <- total).
                    // Dividing is the fallback - a form may already have
                    // worked exchange_amount out at 1.
                    $native = substr($column, strlen($amountPrefix));
                    if (! array_key_exists($native, $attributes) && $column === 'exchange_amount') {
                        $native = 'total';
                    }

                    $model->setAttribute($column, array_key_exists($native, $attributes) && is_numeric($attributes[$native])
                        ? $attributes[$native]
                        : round((float) $value / (float) $rate, 2));
                }
            }
        }
    }

    /** Whether $currencyId is the reporting currency of $companyId (or of the logged-in user's company). */
    public static function isBase($currencyId, $companyId = null): bool
    {
        $base = self::baseFor($companyId);

        return $currencyId && $base && (int) $currencyId === (int) $base;
    }

    private static function baseCurrencyFor(Model $model)
    {
        return self::baseFor($model->getAttribute('company_id'));
    }

    private static function baseFor($companyId)
    {
        if (self::$bases === null) {
            self::$bases = DB::table('companies')->whereNotNull('currency_id')->pluck('currency_id', 'id')->all();
        }

        if ($companyId && isset(self::$bases[$companyId])) {
            return self::$bases[$companyId];
        }

        // No company on the record (bills): every company sharing one
        // reporting currency settles it, else go by the user's company.
        $distinct = array_unique(array_map('intval', self::$bases));
        if (count($distinct) === 1) {
            return reset($distinct);
        }

        $userCompany = optional(optional(optional(Auth::user())->employee)->company)->id;

        return $userCompany && isset(self::$bases[$userCompany]) ? self::$bases[$userCompany] : null;
    }

    /** For tests / long-running processes after a company's currency changes. */
    public static function flush(): void
    {
        self::$bases = null;
    }
}
