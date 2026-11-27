<?php

namespace App\Models\Concerns;

use App\Models\Scopes\CompanyScope;
use App\Support\Tenant;

/**
 * Multi-tenancy: the model only returns rows of the signed-in user's company
 * and new rows are stamped with it. Lift the filter with
 * Model::withoutCompanyScope() only for deliberate cross-company work - e.g.
 * global document numbering behind a unique index. See docs/multi-tenancy-plan.md.
 */
trait BelongsToCompany
{
    /** Override with true to also see shared rows (company_id null). */
    protected static function companyScopeShared(): bool
    {
        return false;
    }

    public static function bootBelongsToCompany(): void
    {
        // Laravel 8 named scopes must be closures; the logic lives in CompanyScope.
        $scope = new CompanyScope(static::companyScopeShared());
        static::addGlobalScope('company', fn (\Illuminate\Database\Eloquent\Builder $builder) => $scope->apply($builder, $builder->getModel()));

        static::creating(function ($model) {
            if (blank($model->company_id) && ! static::companyScopeShared()) {
                $model->company_id = Tenant::id() ?: Tenant::fallbackId();
            }
        });
    }

    public static function withoutCompanyScope()
    {
        return static::withoutGlobalScope('company');
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
