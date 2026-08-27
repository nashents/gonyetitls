<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_locked',
    ];
}
