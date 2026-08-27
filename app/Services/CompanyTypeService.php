<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\DB;

class CompanyTypeService
{
    /**
     * Order in which legacy single-value companies.type is derived from a
     * company's (now possibly multiple) tagged types, so every existing
     * scalar `type` check elsewhere in the app (e.g. Invoices' "Rental"
     * option, the admin-company lookup) keeps working unchanged.
     */
    const LEGACY_PRIORITY = ['Admin', 'Rental', 'Broker', 'Transporter'];

    public function syncTypes(Company $company, array $companyTypeIds): void
    {
        DB::transaction(function () use ($company, $companyTypeIds) {
            $company->company_types()->sync($companyTypeIds);

            $names = $company->company_types()->pluck('name')->all();

            $legacyType = collect(self::LEGACY_PRIORITY)
                ->first(fn ($name) => in_array($name, $names, true))
                ?? ($names[0] ?? null);

            $company->type = $legacyType;
            $company->save();
        });
    }
}
