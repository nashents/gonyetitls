<?php

namespace App\Services\Freight;

use App\Models\FreightCost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FreightCostService
{
    public function __construct(private FreightCostingService $costing)
    {
    }

    public function create(array $data): FreightCost
    {
        return DB::transaction(function () use ($data) {
            $data['exchange_amount'] = $this->computeExchangeAmount($data);
            $cost = FreightCost::create($data);

            $this->costing->recalculateJobTotals($cost->freight_job);

            return $cost;
        });
    }

    public function update(FreightCost $cost, array $data): FreightCost
    {
        return DB::transaction(function () use ($cost, $data) {
            $data['exchange_amount'] = $this->computeExchangeAmount(array_merge($cost->toArray(), $data));
            $cost->update($data);

            $this->costing->recalculateJobTotals($cost->freight_job);

            return $cost;
        });
    }

    public function delete(FreightCost $cost): void
    {
        DB::transaction(function () use ($cost) {
            $job = $cost->freight_job;
            $cost->delete();
            $this->costing->recalculateJobTotals($job);
        });
    }

    public function transitionVerification(FreightCost $cost, string $status, ?string $reason = null): FreightCost
    {
        return DB::transaction(function () use ($cost, $status, $reason) {
            $cost->verification_status = $status;

            if (in_array($status, ['verified', 'approved'], true)) {
                $cost->verified_by_id = Auth::id();
                $cost->verified_at = now();
            }

            if (in_array($status, ['disputed', 'rejected'], true) && $reason !== null) {
                $cost->dispute_reason = $reason;
            }

            $cost->save();

            $this->costing->recalculateJobTotals($cost->freight_job);

            return $cost;
        });
    }

    private function computeExchangeAmount(array $data): float
    {
        $amount = (float) ($data['amount'] ?? 0);
        $rate = (float) ($data['exchange_rate'] ?? 1) ?: 1;

        return round($amount * $rate, 2);
    }
}
