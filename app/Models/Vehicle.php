<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function vehicle_documents(){
        return $this->hasMany('App\Models\VehicleDocument');
    }
    public function bills(){
        return $this->hasMany('App\Models\Bill');
    }
    public function mileages(){
        return $this->hasMany('App\Models\Mileage');
    }
    public function capacities(){
        return $this->hasMany('App\Models\Capacity');
    }
    public function gate_passes(){
        return $this->hasMany('App\Models\GatePass');
    }
    public function incidents(){
        return $this->hasMany('App\Models\Incident');
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
    public function checklists(){
        return $this->hasMany('App\Models\Checklist');
    }
    public function logs(){
        return $this->hasMany('App\Models\Log');
    }
    public function tyre_dispatches(){
        return $this->hasMany('App\Models\TyreDispatch');
    }
    public function tyre_assignments(){
        return $this->hasMany('App\Models\TyreAssignment');
    }
    public function inventory_assignments(){
        return $this->hasMany('App\Models\InventoryAssignment');
    }
    public function allocations(){
        return $this->hasMany('App\Models\Allocation');
    }
    public function bookings(){
        return $this->hasMany('App\Models\Booking');
    }
    public function vehicle_images(){
        return $this->hasMany('App\Models\VehicleImage');
    }
    public function vehicle_type(){
        return $this->belongsTo('App\Models\VehicleType');
    }
    public function inspections(){
        return $this->hasMany('App\Models\Inspection');
    }
    public function vehicle_group(){
        return $this->belongsTo('App\Models\VehicleGroup');
    }
    public function vehicle_model(){
        return $this->belongsTo('App\Models\VehicleModel');
    }
    public function vehicle_make(){
        return $this->belongsTo('App\Models\VehicleMake');
    }
    public function trips(){
        return $this->hasMany('App\Models\Trip');
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
    public function vehicle_assignment(){
        return $this->hasMany('App\Models\VehicleAssignment');
    }
    public function tickets(){
        return $this->hasMany('App\Models\Ticket');
    }
    public function services(){
        return $this->hasMany('App\Models\Service');
    }
    public function cash_flows(){
        return $this->hasMany('App\Models\CashFlow');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function transporter_vehicles(){
        return $this->belongsToMany('App\Models\Transporter');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }

    protected $fillable=[
    'user_id',
    'chasis_number',
    'engine_number',
    'registration_number',
    'vehicle_number',
    'fleet_number',
    'year',
    'color',
    'manufacturer',
    'country_of_orgin',
    'fuel_type',
    'fuel_consumption',
    'fuel_measurement',
    'mileage',
    ];


}
