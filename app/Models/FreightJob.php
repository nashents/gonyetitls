<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FreightJob extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function freight_service_type(){
        return $this->belongsTo('App\Models\FreightServiceType');
    }
    public function salesperson(){
        return $this->belongsTo('App\Models\User', 'salesperson_id');
    }
    public function operations_officer(){
        return $this->belongsTo('App\Models\User', 'operations_officer_id');
    }
    public function clearing_officer(){
        return $this->belongsTo('App\Models\User', 'clearing_officer_id');
    }
    public function origin_country(){
        return $this->belongsTo('App\Models\Country', 'origin_country_id');
    }
    public function destination_country(){
        return $this->belongsTo('App\Models\Country', 'destination_country_id');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function quotation(){
        return $this->belongsTo('App\Models\Quotation');
    }
    public function shipments(){
        return $this->hasMany('App\Models\Shipment');
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }

    protected $casts = [
        'opened_at' => 'datetime',
        'completed_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'job_number',
        'customer_reference',
        'company_id',
        'customer_id',
        'freight_service_type_id',
        'salesperson_id',
        'operations_officer_id',
        'clearing_officer_id',
        'import_export_type',
        'shipment_type',
        'primary_transport_mode',
        'origin',
        'destination',
        'origin_country_id',
        'destination_country_id',
        'incoterm',
        'currency_id',
        'quotation_id',
        'estimated_revenue',
        'estimated_cost',
        'estimated_margin',
        'actual_revenue',
        'actual_cost',
        'actual_margin',
        'status',
        'opened_at',
        'completed_at',
        'closed_at',
        'notes',
    ];
}
