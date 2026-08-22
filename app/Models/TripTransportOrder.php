<?php

namespace App\Models;

use App\Models\Currency;
use App\Models\Deal;
use App\Models\DeliveryNote;
use App\Models\TransportOrder;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class TripTransportOrder extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

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
        'completed',
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
        'exchange_amount'        => 'float',
         'allocated_litreage' => 'float',
        'completed'          => 'boolean',
    ];

    

    
   
    public function units_of_measure(){
        return $this->belongsTo('App\Models\UnitsOfMeasure');
    }
    public function trip_destinations(){
        return $this->hasMany('App\Models\TripDestination');
    }
    public function trip_origins(){
        return $this->hasMany('App\Models\TripOrigin');
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
    public function deal()
    {
        return $this->belongsTo(Deal::class, 'deal_id');
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
