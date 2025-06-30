<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Horse extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function horse_documents(){
        return $this->hasMany('App\Models\HorseDocument');
    }
    public function movements(){
        return $this->hasMany('App\Models\Movement');
    }
      public function dispatches(){
        return $this->hasMany('App\Models\Dispatch');
    }
       public function shifts(){
        return $this->hasMany('App\Models\Shift');
    }
    public function mileages(){
        return $this->hasMany('App\Models\Mileage');
    }
    public function capacities(){
        return $this->hasMany('App\Models\Capacity');
    }
    public function brokers(){
        return $this->belongsToMany('App\Models\Broker');
    }
    public function workshop_services(){
        return $this->hasMany('App\Models\WorkshopService');
    }
    public function incidents(){
        return $this->hasMany('App\Models\Incident');
    }
    public function gate_passes(){
        return $this->hasMany('App\Models\GatePass');
    }
    public function checklists(){
        return $this->hasMany('App\Models\Checklist');
    }
    public function breakdown_assignment(){
        return $this->hasOne('App\Models\BreakdownAssignment');
    }
    public function breakdowns(){
        return $this->hasMany('App\Models\Breakdown');
    }
    public function tyre_assignments(){
        return $this->hasMany('App\Models\TyreAssignment');
    }
    public function bills(){
        return $this->hasMany('App\Models\Bill');
    }
    public function horse_transporters(){
        return $this->belongsToMany('App\Models\Transporter');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }
    public function tyre_dispatches(){
        return $this->hasMany('App\Models\TyreDispatch');
    }
    public function driver(){
        return $this->hasOne('App\Models\Driver');
    }
    public function inventory_assignments(){
        return $this->hasMany('App\Models\InventoryAssignment');
    }
    public function allocations(){
        return $this->hasMany('App\Models\Allocation');
    }
    public function horse_images(){
        return $this->hasMany('App\Models\HorseImage');
    }
    public function horse_type(){
        return $this->belongsTo('App\Models\HorseType');
    }
    public function horse_make(){
        return $this->belongsTo('App\Models\HorseMake');
    }
    public function horse_model(){
        return $this->belongsTo('App\Models\HorseModel');
    }
    public function horse_group(){
        return $this->belongsTo('App\Models\HorseGroup');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function trips(){
        return $this->hasMany('App\Models\Trip')->withTrashed();
    }
    public function fuels(){
        return $this->hasMany('App\Models\Fuel');
    }
    public function trip_returns(){
        return $this->hasMany('App\Models\TripReturn');
    }
    public function fitnesses(){
        return $this->hasMany('App\Models\Fitness');
    }
    public function transport_orders(){
        return $this->hasMany('App\Models\TransportOrder');
    }
    public function inventory_requisitions(){
        return $this->hasMany('App\Models\InventoryRequisition');
    }
    public function inventory_dispatches(){
        return $this->hasMany('App\Models\InventoryDispatch');
    }
    public function ticket_inventories(){
        return $this->hasMany('App\Models\TicketInventory');
    }
    public function assignments(){
        return $this->hasMany('App\Models\Assignment');
    }
    public function trailer_assignments(){
        return $this->hasMany('App\Models\TrailerAssignment');
    }
    public function tickets(){
        return $this->hasMany('App\Models\Ticket');
    }
    public function services(){
        return $this->hasMany('App\Models\Service');
    }
    public function bookings(){
        return $this->hasMany('App\Models\Booking');
    }
    public function inspections(){
        return $this->hasMany('App\Models\Inspection');
    }
    public function cash_flows(){
        return $this->hasMany('App\Models\CashFlow');
    }
    protected $fillable=[
    'user_id',
    'horse_make_id',
    'horse_model_id',
    'horse_number',
    'fleet_number',
    'transporter_id',
    'model',
    'chasis_number',
    'engine_number',
    'registration_number',
    'horse',
    'year',
    'color',
    'manufacturer',
    'country_of_orgin',
    'fuel_type',
    'fuel_consumption',
    'fuel_measurement',
    'mileage',
    'no_of_wheels',
    ];


}
