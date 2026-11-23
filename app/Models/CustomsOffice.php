<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Customs offices for the Customs Declaration dropdown. Declarations store
 * the office name (customs_declarations.customs_office), not this id.
 */
class CustomsOffice extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['name', 'country_id', 'is_active', 'created_by'];

    protected $casts = ['is_active' => 'boolean'];

    public function country(){
        return $this->belongsTo('App\Models\Country');
    }

    /** Existing office with this name (case/space-insensitive), or a new one. */
    public static function findOrCreateByName(string $name, ?int $countryId = null, ?int $userId = null): self
    {
        $name = preg_replace('/\s+/', ' ', trim($name));

        return static::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first()
            ?? static::create(['name' => $name, 'country_id' => $countryId, 'is_active' => true, 'created_by' => $userId]);
    }
}
