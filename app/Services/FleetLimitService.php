<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Transporter;

class FleetLimitService
{
    /**
     * Asset types (relation names on Transporter, singular) that count
     * toward this company's fleet band, based on its fleet_composition.
     */
    public function assetTypesCounted(Company $company): array
    {
        $types = ['horse', 'vehicle'];

        if ($company->countsTrailersInFleet()) {
            $types[] = 'trailer';
        }

        return $types;
    }

    public function isAssetTypeCounted(Company $company, string $assetType): bool
    {
        return in_array($assetType, $this->assetTypesCounted($company), true);
    }

    /**
     * The company's fleet cap, or null when unrestricted (no plan set,
     * or the open-ended "200>" band).
     */
    public function maxFleetCount(Company $company): ?int
    {
        $plan = $company->plan;

        if ($plan === null || $plan === '') {
            return null;
        }

        $plan = (int) $plan;

        if ($plan >= 201) {
            return null;
        }

        return $plan;
    }

    /**
     * The company's current live fleet count, summed across all of its
     * transporters, for whichever asset types its composition counts.
     */
    public function currentFleetCount(Company $company): int
    {
        $relations = array_map(fn (string $type) => $type.'s', $this->assetTypesCounted($company));

        $transporters = $company->transporters()->withCount($relations)->get();

        return $transporters->reduce(function (int $carry, Transporter $transporter) use ($relations) {
            foreach ($relations as $relation) {
                $carry += $transporter->{$relation.'_count'};
            }

            return $carry;
        }, 0);
    }

    public function remainingCapacity(Company $company): ?int
    {
        $max = $this->maxFleetCount($company);

        if ($max === null) {
            return null;
        }

        return max(0, $max - $this->currentFleetCount($company));
    }

    public function canAdd(Company $company, string $assetType, int $qty = 1): bool
    {
        if (! $this->isAssetTypeCounted($company, $assetType)) {
            return true;
        }

        $remaining = $this->remainingCapacity($company);

        return $remaining === null || $remaining >= $qty;
    }

    public function companyForTransporter(?Transporter $transporter): ?Company
    {
        return $transporter?->company;
    }
}
