<?php

namespace App\Imports\Concerns;

use App\Models\Company;
use App\Services\FleetLimitService;

trait ChecksFleetLimit
{
    protected array $fleetCompanyCounts = [];
    protected array $fleetCompanyMax = [];

    public array $skippedForLimit = [];

    protected function canAddToFleet(Company $company, string $assetType): bool
    {
        $service = app(FleetLimitService::class);

        if (! $service->isAssetTypeCounted($company, $assetType)) {
            return true;
        }

        if (! array_key_exists($company->id, $this->fleetCompanyMax)) {
            $this->fleetCompanyMax[$company->id] = $service->maxFleetCount($company);
            $this->fleetCompanyCounts[$company->id] = $service->currentFleetCount($company);
        }

        $max = $this->fleetCompanyMax[$company->id];

        return $max === null || $this->fleetCompanyCounts[$company->id] < $max;
    }

    protected function registerFleetAddition(Company $company): void
    {
        if (array_key_exists($company->id, $this->fleetCompanyCounts)) {
            $this->fleetCompanyCounts[$company->id]++;
        }
    }

    protected function recordFleetLimitSkip(string $registrationNumber, Company $company): void
    {
        $this->skippedForLimit[] = "Skipped {$registrationNumber}: fleet limit reached for {$company->name}.";
    }
}
