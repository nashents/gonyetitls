<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTripPaymentsTable extends Migration
{
    /**
     * One row per payment received against a trip, so cash-basis reports can
     * place each payment in the month it was actually paid. trips.amount_paid /
     * exchange_amount_paid / paid_at become totals recomputed from these rows.
     *
     * amount        - what was received, in currency_id
     * exchange_rate - 1 payment currency = ? trip currency (1 when the same)
     * trip_amount   - amount in the trip's currency (adds to trips.amount_paid)
     * exchange_amount - amount in the company's reporting currency
     *
     * Existing paid trips are backfilled: invoice payment line splits keep
     * their payment's date, anything else left in amount_paid becomes one
     * row dated the trip's paid_at.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trip_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
            $table->foreignId('invoice_payment_item_id')->nullable()->constrained('invoice_payment_items')->cascadeOnDelete();
            $table->string('source')->default('manual');
            $table->date('payment_date');
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('exchange_rate', 15, 6)->nullable();
            $table->decimal('trip_amount', 15, 2);
            $table->decimal('exchange_amount', 15, 2)->nullable();
            $table->string('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['trip_id', 'payment_date']);
            $table->index('payment_date');
        });

        $this->backfill();
    }

    protected function backfill(): void
    {
        $baseCurrencies = DB::table('companies')->pluck('currency_id', 'id');
        $defaultBase = $baseCurrencies->first();
        $now = now();

        $trips = DB::table('trips')
            ->where(function ($q) {
                $q->where('amount_paid', '>', 0)
                  ->orWhereIn('id', DB::table('invoice_payment_items')->whereNotNull('trip_id')->select('trip_id'));
            })
            ->get(['id', 'company_id', 'currency_id', 'exchange_rate', 'amount_paid', 'paid_at', 'paid_by', 'updated_at']);

        foreach ($trips as $trip) {
            $base = $baseCurrencies[$trip->company_id] ?? $defaultBase;
            $tripRate = is_numeric($trip->exchange_rate) && $trip->exchange_rate > 0 ? (float) $trip->exchange_rate : null;
            $toBase = function (float $tripAmount) use ($trip, $base, $tripRate) {
                if (! $trip->currency_id || (int) $trip->currency_id === (int) $base) {
                    return round($tripAmount, 2);
                }
                return $tripRate ? round($tripAmount * $tripRate, 2) : null;
            };

            $rows = [];
            $covered = 0.0;

            $splits = DB::table('invoice_payment_items as ipi')
                ->join('invoice_payments as ip', 'ip.id', '=', 'ipi.invoice_payment_id')
                ->join('invoices as i', 'i.id', '=', 'ipi.invoice_id')
                ->leftJoin('payments as p', 'p.id', '=', 'ipi.payment_id')
                ->where('ipi.trip_id', $trip->id)
                ->whereNull('ip.deleted_at')
                ->whereNull('p.deleted_at')
                ->get(['ipi.id', 'ipi.amount', 'ipi.trip_amount', 'i.currency_id', 'p.date', 'p.exchange_rate', 'p.user_id', 'ipi.created_at']);

            foreach ($splits as $split) {
                $amount = (float) $split->amount;
                $tripAmount = (float) ($split->trip_amount ?? $amount);
                if ($amount <= 0) {
                    continue;
                }

                if ((int) $split->currency_id === (int) $base) {
                    $exchange = round($amount, 2);
                } elseif (is_numeric($split->exchange_rate) && $split->exchange_rate > 0) {
                    $exchange = round($amount * (float) $split->exchange_rate, 2);
                } else {
                    $exchange = $toBase($tripAmount);
                }

                $rows[] = [
                    'trip_id' => $trip->id,
                    'invoice_payment_item_id' => $split->id,
                    'source' => 'invoice',
                    'payment_date' => $this->day($split->date ?? $split->created_at ?? $trip->paid_at),
                    'currency_id' => $split->currency_id,
                    'amount' => $amount,
                    'exchange_rate' => round($tripAmount / $amount, 6),
                    'trip_amount' => $tripAmount,
                    'exchange_amount' => $exchange,
                    'notes' => null,
                    'user_id' => $split->user_id ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $covered += $tripAmount;
            }

            $remainder = round((float) $trip->amount_paid - $covered, 2);
            if ($remainder > 0.01) {
                $rows[] = [
                    'trip_id' => $trip->id,
                    'invoice_payment_item_id' => null,
                    'source' => 'manual',
                    'payment_date' => $this->day($trip->paid_at ?? $trip->updated_at),
                    'currency_id' => $trip->currency_id,
                    'amount' => $remainder,
                    'exchange_rate' => 1,
                    'trip_amount' => $remainder,
                    'exchange_amount' => $toBase($remainder),
                    'notes' => 'Recorded before payment history was tracked',
                    'user_id' => $trip->paid_by,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows) {
                DB::table('trip_payments')->insert($rows);
            }
        }
    }

    protected function day($value): string
    {
        try {
            return $value ? Carbon::parse($value)->toDateString() : now()->toDateString();
        } catch (\Throwable $e) {
            return now()->toDateString();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trip_payments');
    }
}
