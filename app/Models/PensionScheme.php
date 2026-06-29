<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PensionScheme extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'code', 'type',
        'administrator_name', 'fund_number',
        'allows_voluntary_additional_contributions',
        'active', 'description', 'created_by',
    ];

    protected $casts = [
        'allows_voluntary_additional_contributions' => 'boolean',
        'active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function rates()
    {
        return $this->hasMany(PensionSchemeRate::class);
    }

    public function enrollments()
    {
        return $this->hasMany(EmployeePensionEnrollment::class);
    }

    public function rateOn(string $date): ?PensionSchemeRate
    {
        return $this->rates()
            ->where('effective_from', '<=', $date)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date))
            ->orderByDesc('effective_from')
            ->first();
    }
}
