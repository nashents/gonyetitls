<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChargeRateTier extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function shipping_line_vendor(){
        return $this->belongsTo('App\Models\Vendor', 'shipping_line_vendor_id');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }

    protected $casts = [
        'day_from' => 'integer',
        'day_to' => 'integer',
        'rate' => 'float',
    ];

    protected $fillable = [
        'charge_type',
        'shipping_line_vendor_id',
        'day_from',
        'day_to',
        'rate',
        'currency_id',
    ];
}
