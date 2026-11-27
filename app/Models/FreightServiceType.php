<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FreightServiceType extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function freight_jobs(){
        return $this->hasMany('App\Models\FreightJob');
    }

    /**
     * Types offered on create/edit forms: active ones, plus $keep (the record's
     * current type) so editing an old job never silently drops a deactivated
     * type. Filters and reports use the unfiltered list.
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

    protected $casts = [
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'user_id',
        'description',
        'is_active',
        'is_locked',
    ];
}
