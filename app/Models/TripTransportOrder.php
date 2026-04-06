<?php

namespace App\Models;

use App\Models\Currency;
use App\Models\DeliveryNote;
use App\Models\TransportOrder;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripTransportOrder extends Model
{
    use HasFactory, SoftDeletes;

     protected $table = 'trip_transport_orders';

    protected $fillable = [
        'trip_id',
        'transport_order_id',

        'allocated_quantity',
        'allocated_weight',
        'allocated_volume',

        'delivered_quantity',
        'delivered_weight',
        'delivered_volume',

        'sequence_no',
        'status',
        'notes',
    ];

    protected $casts = [
        'allocated_quantity' => 'decimal:3',
        'allocated_weight'   => 'decimal:3',
        'allocated_volume'   => 'decimal:3',
        'delivered_quantity' => 'decimal:3',
        'delivered_weight'   => 'decimal:3',
        'delivered_volume'   => 'decimal:3',
        'sequence_no'        => 'integer',
    ];

    
   
    public function units_of_measure(){
        return $this->belongsTo('App\Models\UnitsOfMeasure');
    }
    public function trip_destinations(){
        return $this->hasMany('App\Models\TripDestination');
    }

     public function invoice_items(){
        return $this->hasMany('App\Models\InvoiceItem');
    }
    public function delivery_note()
    {
        return $this->hasOne(DeliveryNote::class, 'trip_transport_order_id');
    }

    public function transport_order()
    {
        return $this->belongsTo(TransportOrder::class, 'transport_order_id');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }
}
