<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ChargeType extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function freight_costs(){
        return $this->hasMany('App\Models\FreightCost');
    }
    public function freight_charges(){
        return $this->hasMany('App\Models\FreightCharge');
    }
    public function freight_rate_cards(){
        return $this->hasMany('App\Models\FreightRateCard');
    }
    public function revenue_account(){
        return $this->belongsTo('App\Models\Account', 'revenue_account_id');
    }
    public function expense_account(){
        return $this->belongsTo('App\Models\Account', 'expense_account_id');
    }

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_locked',
        'is_active',
        'revenue_account_id',
        'expense_account_id',
        'exposure_code',
        'start_milestone',
        'stop_milestone',
        'is_line_specific',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'is_active' => 'boolean',
        'is_line_specific' => 'boolean',
    ];

    /**
     * Types offered on cost/charge lines and rate cards: active ones, plus
     * $keep (the line's current type) so editing an old line never silently
     * drops a deactivated type.
     */
    public function scopeSelectable($query, $keep = null)
    {
        return $query->where(function ($q) use ($keep) {
            $q->where('is_active', true);
            if (filled($keep)) {
                $q->orWhere('id', $keep);
            }
        })->orderBy('name', 'asc');
    }

    /** Cost lines, charge lines or rate cards using this type (deleted jobs' lines included). */
    public function usageCounts(): array
    {
        return [
            'cost line(s)' => FreightCost::withTrashed()->where('charge_type_id', $this->id)->count(),
            'charge line(s)' => FreightCharge::withTrashed()->where('charge_type_id', $this->id)->count(),
            'rate card(s)' => FreightRateCard::where('charge_type_id', $this->id)->count(),
        ];
    }

    /** Charge types that track container free time (selectable in Charge Config). */
    public function scopeTracksFreeTime($query)
    {
        return $query->whereNotNull('exposure_code');
    }

    public function hasFreeTime(): bool
    {
        return $this->exposure_code !== null;
    }

    /** Port Storage / Demurrage / Detention: tracked by PortExposureService's own milestones. */
    public function isBuiltInFreeTime(): bool
    {
        return $this->exposure_code !== null
            && array_key_exists($this->exposure_code, ContainerChargeExposure::CHARGE_TYPES);
    }

    /** Whether a free day policy, rate tier or container exposure uses this type's code. */
    public function freeTimeInUse(): bool
    {
        if ($this->exposure_code === null) {
            return false;
        }

        return ChargeFreeDayPolicy::where('charge_type', $this->exposure_code)->exists()
            || ChargeRateTier::where('charge_type', $this->exposure_code)->exists()
            || ContainerChargeExposure::where('charge_type', $this->exposure_code)->exists();
    }

    /** Unique code from the name; never reuses a built-in or any existing (incl. deleted) code. */
    public static function makeExposureCode(string $name): string
    {
        $base = Str::slug($name, '_') ?: 'charge';
        $code = $base;
        $i = 2;

        while (array_key_exists($code, ContainerChargeExposure::CHARGE_TYPES)
            || static::withTrashed()->where('exposure_code', $code)->exists()) {
            $code = $base . '_' . $i++;
        }

        return $code;
    }
}
