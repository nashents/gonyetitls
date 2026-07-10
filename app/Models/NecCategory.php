<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class NecCategory extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'company_id', 'country', 'name', 'code', 'description', 'active', 'created_by',
    ];

    protected $casts = ['active' => 'boolean'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function rates()
    {
        return $this->hasMany(NecRate::class);
    }

    public function employeeAssignments()
    {
        return $this->hasMany(EmployeeNecAssignment::class);
    }

    public function rateOn(string $date): ?NecRate
    {
        return $this->rates()
            ->where('effective_from', '<=', $date)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date))
            ->orderByDesc('effective_from')
            ->first();
    }
}
