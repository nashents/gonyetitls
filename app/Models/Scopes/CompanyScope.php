<?php

namespace App\Models\Scopes;

use App\Support\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Multi-tenancy filter: only the current company's rows (App\Support\Tenant).
 * $shared = true also keeps rows with no company (national / default lookups).
 */
class CompanyScope implements Scope
{
    public function __construct(private bool $shared = false)
    {
    }

    public function apply(Builder $builder, Model $model)
    {
        if (! Tenant::enforced()) {
            return;
        }

        $companyId = Tenant::id();
        if ($companyId === null) {
            return; // no tenant context (artisan / queue / seeders)
        }

        $column = $model->qualifyColumn('company_id');

        if ($this->shared) {
            $builder->where(fn ($q) => $q->where($column, $companyId)->orWhereNull($column));
        } else {
            $builder->where($column, $companyId);
        }
    }
}
