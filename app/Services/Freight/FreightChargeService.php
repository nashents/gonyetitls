<?php

namespace App\Services\Freight;

use App\Models\FreightCharge;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FreightChargeService
{
    public function __construct(private FreightCostingService $costing)
    {
    }

    public function create(array $data): FreightCharge
    {
        return DB::transaction(function () use ($data) {
            $data['exchange_amount'] = $this->computeExchangeAmount($data);
            $charge = FreightCharge::create($data);

            $this->costing->recalculateJobTotals($charge->freight_job);

            return $charge;
        });
    }

    public function update(FreightCharge $charge, array $data): FreightCharge
    {
        return DB::transaction(function () use ($charge, $data) {
            $data['exchange_amount'] = $this->computeExchangeAmount(array_merge($charge->toArray(), $data));
            $charge->update($data);

            $this->costing->recalculateJobTotals($charge->freight_job);

            return $charge;
        });
    }

    public function delete(FreightCharge $charge): void
    {
        DB::transaction(function () use ($charge) {
            $job = $charge->freight_job;
            $charge->delete();
            $this->costing->recalculateJobTotals($job);
        });
    }

    public function transitionStatus(FreightCharge $charge, string $status): FreightCharge
    {
        return DB::transaction(function () use ($charge, $status) {
            $charge->status = $status;

            if ($status === 'approved') {
                $charge->approved_by_id = Auth::id();
                $charge->approved_at = now();
            }

            $charge->save();

            $this->costing->recalculateJobTotals($charge->freight_job);

            return $charge;
        });
    }

    private function computeExchangeAmount(array $data): float
    {
        $amount = (float) ($data['amount'] ?? 0);
        $rate = (float) ($data['exchange_rate'] ?? 1) ?: 1;

        return round($amount * $rate, 2);
    }
}
