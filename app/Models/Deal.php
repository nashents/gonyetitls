<?php

namespace App\Models;

use App\Models\Cargo;
use App\Models\Company;
use App\Models\Customer;
use App\Models\UnitsOfMeasure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Deal extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function transport_orders()
    {
        return $this->hasMany(TransportOrder::class);
    }
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
    public function trip_transport_orders()
    {
        return $this->hasMany(TripTransportOrder::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    public function units_of_measure()
    {
        return $this->belongsTo(UnitsOfMeasure::class, 'units_of_measure_id');
    }
}
