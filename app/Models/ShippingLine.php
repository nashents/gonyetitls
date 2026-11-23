<?php

namespace App\Models;

use App\Services\Freight\ShippingLineVendorService;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingLine extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }

    /**
     * Every shipping line is paired with a vendor (attached by name or
     * created) and renames flow both ways - see ShippingLineVendorService.
     */
    protected static function booted()
    {
        static::saving(fn (ShippingLine $line) => app(ShippingLineVendorService::class)->ensureVendor($line));

        static::updated(function (ShippingLine $line) {
            if ($line->wasChanged('name')) {
                app(ShippingLineVendorService::class)->shippingLineRenamed($line);
            }
        });
    }

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'vendor_id',
        'is_active',
    ];
}
