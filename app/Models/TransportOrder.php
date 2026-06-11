<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransportOrder extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

   public function trips()
    {
        return $this->belongsToMany(
            Trip::class,
            'trip_transport_orders',
            'transport_order_id',
            'trip_id'
        );
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function trip_origins(){
        return $this->hasMany('App\Models\TripOrigin');
    }
     public function trip_destinations(){
        return $this->hasMany('App\Models\TripDestination');
    }
    public function quotation(){
        return $this->belongsTo('App\Models\Quotation');
    }
    public function cargo(){
        return $this->belongsTo('App\Models\Cargo');
    }
    public function trip_type(){
        return $this->belongsTo('App\Models\TripType');
    }
    public function units_of_measure(){
        return $this->belongsTo('App\Models\UnitsOfMeasure');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function consignee(){
        return $this->belongsTo('App\Models\Consignee');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }
    public function loading_point(){
        return $this->belongsTo('App\Models\LoadingPoint');
    }
    public function offloading_point(){
        return $this->belongsTo('App\Models\OffloadingPoint');
    }
    public function fromDestination()
    {
        return $this->belongsTo(\App\Models\Destination::class, 'from');
    }
    public function toDestination()
    {
        return $this->belongsTo(\App\Models\Destination::class, 'to');
    }
     public function deal(){
        return $this->belongsTo('App\Models\Deal');
    }

    protected $casts = [
        'multiple_destinations'      => 'boolean',
        'deal_id' => 'integer',
    ];

    protected $fillable = [
    'transport_order_number',
    'custom_ref',
    'bill_of_entry',
    'user_id',
    'deal_id',
    'company_id',
    'quotation_id',
    'with_customer_rates',
    'with_transporter_rates',
    'customer_id',
    'consignee_id',
    'freight_calculation',
    'calculation_measurement',
    'currency_id',
    'cargo_id',
    'trip_type_id',
    'defined_customer_rate_id',
    'defined_transporter_rate_id',
    'from',
    'to',
    'offloading_point_id',
    'loading_point_id',
    'start_date',
    'end_date',
    'cargo_details',
    'multiple_destinations',
    'quantity',
    'litreage',
    'units_of_measure_id',
    'weight',
    'status',
    'rate',
    'freight',
    'transporter_rate',
    'transporter_freight',
    'exchange_rate',
    'exchange_customer_freight',
    'distance',
    'authorized_by_id',
    'authorization',
    'authorization_date',
    'comments',
];


}
