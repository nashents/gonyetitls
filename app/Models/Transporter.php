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

    /** Own fleet: the company's default transporter. */
    public const FLEET_OWN = 'own';
    /** Subcontractor run like the own fleet (bookings, workshop, stores) and counted toward the plan band. */
    public const FLEET_MANAGED = 'managed';
    /** Subcontractor used on trips only; its assets are hidden from operational modules. */
    public const FLEET_ONCE_OFF = 'once_off';

    public const FLEET_TYPES = [
        self::FLEET_OWN => 'Own Fleet',
        self::FLEET_MANAGED => 'Managed',
        self::FLEET_ONCE_OFF => 'Once-off',
    ];

    /** Fleet types whose assets appear in operational modules and count toward the plan. */
    public const OPERATIONAL_FLEET_TYPES = [self::FLEET_OWN, self::FLEET_MANAGED];

    protected static function booted()
    {
        // `default` and fleet_type stay in step: default <=> own. A demoted
        // own fleet becomes managed so its assets stay operational.
        static::saving(function (Transporter $transporter) {
            if ($transporter->isDirty('default')) {
                if ($transporter->default) {
                    $transporter->fleet_type = self::FLEET_OWN;
                } elseif ($transporter->fleet_type === self::FLEET_OWN) {
                    $transporter->fleet_type = self::FLEET_MANAGED;
                }
            } elseif ($transporter->isDirty('fleet_type')) {
                $transporter->default = $transporter->fleet_type === self::FLEET_OWN;
            }

            if (! $transporter->fleet_type) {
                $transporter->fleet_type = $transporter->default ? self::FLEET_OWN : self::FLEET_ONCE_OFF;
            }
        });
    }

    public function scopeOperational($query)
    {
        return $query->whereIn('fleet_type', self::OPERATIONAL_FLEET_TYPES);
    }

    public function isOperational(): bool
    {
        return in_array($this->fleet_type ?: self::FLEET_ONCE_OFF, self::OPERATIONAL_FLEET_TYPES, true);
    }

    public function getFleetTypeLabelAttribute(): string
    {
        return self::FLEET_TYPES[$this->fleet_type] ?? self::FLEET_TYPES[self::FLEET_ONCE_OFF];
    }

    public function bookings(){
        return $this->hasMany('App\Models\Booking');
    }

    /** Sage Intacct link (entity_type transporter_project) for the sync badge/status. */
    public function sageMapping(){
        return $this->hasOne(\App\Models\IntegrationMapping::class, 'local_id')
            ->where('entity_type', 'transporter_project');
    }
     public function rates(){
        return $this->hasMany('App\Models\Rate');
    }
    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }
       public function shifts(){
        return $this->hasMany('App\Models\Shift');
    }
    public function incidents(){
        return $this->hasMany('App\Models\Incident');
    }
     public function rentals(){
        return $this->hasMany('App\Models\Rental');
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

    /** The Sage customer this transporter's job cards/invoices bill to. */
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
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
        'default',
        'fleet_type',
        'custom_ref',
        'customer_id',
    ];

    protected $casts = [
        'default' => 'boolean',
    ];
}
