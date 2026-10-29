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
     * Whether assets under this transporter count toward the company's plan
     * band: the own fleet (default transporter) and managed subcontractors
     * are licensed; once-off subcontractors are not.
     */
    public function isTransporterCounted(Company $company, Transporter $transporter): bool
    {
        if ((int) $transporter->company_id !== (int) $company->id) {
            return false;
        }

        return $transporter->isOperational();
    }

    /**
     * Per fleet type (own / managed) live asset counts for whichever asset
     * types the company's composition counts, e.g. ['own' => 12, 'managed' => 5].
     */
    public function fleetCountsByType(Company $company): array
    {
        $relations = array_map(fn (string $type) => $type.'s', $this->assetTypesCounted($company));

        $counts = array_fill_keys(Transporter::OPERATIONAL_FLEET_TYPES, 0);

        $company->transporters()->operational()->withCount($relations)->get()
            ->each(function (Transporter $transporter) use ($relations, &$counts) {
                foreach ($relations as $relation) {
                    $counts[$transporter->fleet_type] += $transporter->{$relation.'_count'};
                }
            });

        return $counts;
    }

    /**
     * The company's current live fleet count: own fleet plus managed
     * transporters, for whichever asset types its composition counts.
     */
    public function currentFleetCount(Company $company): int
    {
        return array_sum($this->fleetCountsByType($company));
    }

    /**
     * Assets a transporter would add to the fleet count if it became managed.
     */
    public function transporterFleetCount(Company $company, Transporter $transporter): int
    {
        return collect($this->assetTypesCounted($company))
            ->sum(fn (string $type) => $transporter->{$type.'s'}()->count());
    }

    public function remainingCapacity(Company $company): ?int
    {
        $max = $this->maxFleetCount($company);

        if ($max === null) {
            return null;
        }

        return max(0, $max - $this->currentFleetCount($company));
    }

    public function canAdd(Company $company, string $assetType, int $qty = 1, ?Transporter $transporter = null): bool
    {
        if (! $this->isAssetTypeCounted($company, $assetType)) {
            return true;
        }

        if ($transporter && ! $this->isTransporterCounted($company, $transporter)) {
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
