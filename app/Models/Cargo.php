<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cargo extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function capacities(){
        return $this->hasMany('App\Models\Capacity');
    }
    public function shifts(){
        return $this->hasMany('App\Models\Shift');
    }
    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }
    public function incidents(){
        return $this->hasMany('App\Models\Incident');
    }
    public function rates(){
        return $this->hasMany('App\Models\Rate');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function transporters(){
        return $this->belongsToMany('App\Models\Transporter');
    }
    public function brokers(){
        return $this->belongsToMany('App\Models\Broker');
    }
    public function invoice_products(){
        return $this->hasMany('App\Models\InvoiceProduct');
    }
    public function quotation_products(){
        return $this->hasMany('App\Models\QuotationProduct');
    }
    public function trip_returns(){
        return $this->hasMany('App\Models\TripReturn');
    }

    protected $fillable = [
        'user_id',
        'name',
        'group',
        'type',
        'measurement',
        'risk',
    ];
}
