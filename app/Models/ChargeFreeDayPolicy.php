<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChargeFreeDayPolicy extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function shipping_line_vendor(){
        return $this->belongsTo('App\Models\Vendor', 'shipping_line_vendor_id');
    }
    public function shipping_line(){
        return $this->belongsTo('App\Models\ShippingLine');
    }

    protected $casts = [
        'free_days' => 'integer',
    ];

    protected $fillable = [
        'charge_type',
        'shipping_line_vendor_id',
        'shipping_line_id',
        'free_days',
    ];
}
