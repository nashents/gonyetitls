<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transporter extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }
    public function incidents(){
        return $this->hasMany('App\Models\Incident');
    }
    public function workshop_services(){
        return $this->hasMany('App\Models\WorkshopService');
    }
    public function trailer_links(){
        return $this->hasMany('App\Models\TrailerLink');
    }
    public function breakdown_assignment(){
        return $this->hasOne('App\Models\BreakdownAssignment');
    }
    public function bills(){
        return $this->hasMany('App\Models\Bill');
    }
    public function breakdowns(){
        return $this->hasMany('App\Models\Breakdown');
    }
    public function payments(){
        return $this->hasMany('App\Models\Payment');
    }
    public function corridors(){
        return $this->belongsToMany('App\Models\Corridor');
    }
    public function cargos(){
        return $this->belongsToMany('App\Models\Cargo');
    }

    public function transport_orders(){
        return $this->hasMany('App\Models\TransportOrder');
    }
    public function contacts(){
        return $this->hasMany('App\Models\Contact');
    }
    public function assignments(){
        return $this->hasMany('App\Models\Assignment');
    }
    public function trailer_assignments(){
        return $this->hasMany('App\Models\TrailerAssignment');
    }
    public function horse_transporters(){
        return $this->belongsToMany('App\Models\Horse');
    }
    public function driver_transporters(){
        return $this->belongsToMany('App\Models\Driver');
    }
    public function trailer_transporters(){
        return $this->belongsToMany('App\Models\Trailer');
    }
    public function transporter_vehicles(){
        return $this->belongsToMany('App\Models\Vehicle');
    }
    public function horses(){
        return $this->hasMany('App\Models\Horse');
    }
    public function vehicles(){
        return $this->hasMany('App\Models\Vehicle');
    }
    public function drivers(){
        return $this->hasMany('App\Models\Driver');
    }
    public function trailers(){
        return $this->hasMany('App\Models\Trailer');
    }

    public function documents(){
        return $this->hasMany('App\Models\Document');
    }
    public function transporter_contacts(){
        return $this->hasMany('App\Models\TransporterContact');
    }
    
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }

    public function quotations(){
        return $this->hasMany('App\Models\Quotation');
    }
    public function invoices(){
        return $this->hasMany('App\Models\Invoice');
    }

    public function trip_returns(){
        return $this->hasMany('App\Models\TripReturn');
    }

    protected $fillable = [
        'user_id',
        'name',
        'transport_number',
        'contact_name',
        'contact_surname',
        'email',
        'phonenumber',
        'worknumber',
        'country',
        'city',
        'suburb',
        'status',
        'street_address',
    ];
}
