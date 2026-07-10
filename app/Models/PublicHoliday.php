<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class PublicHoliday extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'company_id', 'country', 'name', 'date', 'recurring_annually', 'active',
    ];

    protected $casts = [
        'date'               => 'date',
        'recurring_annually' => 'boolean',
        'active'             => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if a given Carbon date falls on a public holiday.
     */
    public static function isHoliday(Carbon $date, string $country = 'ZW', ?int $companyId = null): bool
    {
        return static::where('country', $country)
            ->where('active', true)
            ->where(function ($q) use ($companyId) {
                $q->whereNull('company_id');
                if ($companyId) {
                    $q->orWhere('company_id', $companyId);
                }
            })
            ->where(function ($q) use ($date) {
                $q->where(function ($r) use ($date) {
                    $r->where('recurring_annually', true)
                      ->whereMonth('date', $date->month)
                      ->whereDay('date', $date->day);
                })->orWhere(function ($r) use ($date) {
                    $r->where('recurring_annually', false)
                      ->whereDate('date', $date->toDateString());
                });
            })
            ->exists();
    }
}
