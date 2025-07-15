<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Trip extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    

    public function mileages(){
        return $this->hasMany('App\Models\Mileage');
    }
    public function emptyruns(){
        return $this->hasMany('App\Models\EmptyRun');
    }
    public function trip_type(){
        return $this->belongsTo('App\Models\TripType');
    }
    public function shift(){
        return $this->belongsTo('App\Models\Shift');
    }
    public function rate(){
        return $this->belongsTo('App\Models\Rate');
    }
    public function consignee(){
        return $this->belongsTo('App\Models\Consignee');
        }
    public function gate_pass(){
        return $this->hasOne('App\Models\GatePass');
    }
    public function requisitions(){
        return $this->hasMany('App\Models\Requisition');
    }
    public function breakdowns(){
        return $this->hasMany('App\Models\Breakdown');
    }
    public function breakdown_assignments(){
        return $this->hasMany('App\Models\BreakdownAssignment');
    }
    
    public function bills(){
        return $this->hasMany('App\Models\Bill');
    }
    public function recoveries(){
        return $this->hasMany('App\Models\Recovery');
    }
    public function invoice_items(){
        return $this->hasMany('App\Models\InvoiceItem');
    }
    public function quotation(){
        return $this->belongsTo('App\Models\Quotation');
    }
    public function borders(){
        return $this->belongsToMany('App\Models\Border');
    }
    public function border(){
        return $this->belongsTo('App\Models\Border');
    }
    public function clearing_agents(){
        return $this->belongsToMany('App\Models\ClearingAgent');
    }
    public function clearing_agent(){
        return $this->belongsTo('App\Models\ClearingAgent');
    }
    public function trip_positions(){
        return $this->hasMany('App\Models\TripPosition');
    }
    public function trip_destinations(){
        return $this->hasMany('App\Models\TripDestination');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function trip_locations(){
        return $this->hasMany('App\Models\TripLocation');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse')->withTrashed();
    }
    public function truck_stops(){
        return $this->belongsToMany('App\Models\TruckStop');
    }
    public function trailers(){
        return $this->belongsToMany('App\Models\Trailer');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function commission(){
        return $this->hasOne('App\Models\Commission');
    }
    public function cargo(){
        return $this->belongsTo('App\Models\Cargo');
    }
    public function offloading_point(){
        return $this->belongsTo('App\Models\OffloadingPoint');
    }
    public function loading_point(){
        return $this->belongsTo('App\Models\LoadingPoint');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }
    public function broker(){
        return $this->belongsTo('App\Models\Broker');
    }
    public function route(){
        return $this->belongsTo('App\Models\Route');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function agent(){
        return $this->belongsTo('App\Models\Agent');
    }
    public function trip_group(){
        return $this->belongsTo('App\Models\TripGroup');
    }
    public function destination(){
        return $this->belongsTo('App\Models\Destination');
    }
    public function trip_return(){
        return $this->hasOne('App\Models\TripReturn');
    }
    public function incidents(){
        return $this->hasMany('App\Models\Incident');
    }
    public function cash_flows(){
        return $this->hasMany('App\Models\CashFlow');
    }
    public function payments(){
        return $this->hasMany('App\Models\Payment');
    }
    public function transport_order(){
        return $this->hasOne('App\Models\TransportOrder');
    }
    public function delivery_note(){
        return $this->hasOne('App\Models\DeliveryNote');
    }
    public function trip_expenses(){
        return $this->hasMany('App\Models\TripExpense');
    }
    public function trip_documents(){
        return $this->hasMany('App\Models\TripDocument');
    }
    public function invoices(){
        return $this->hasMany('App\Models\Invoice');
    }
    public function invoice_trips(){
        return $this->hasMany('App\Models\InvoiceTrip');
    }
    public function receipts(){
        return $this->hasMany('App\Models\Receipt');
    }
    public function driver_allowances(){
        return $this->hasMany('App\Models\AllowanceDriver');
    }
    public function fuels(){
        return $this->hasMany('App\Models\Fuel');
    }
    public function fuel(){
        return $this->hasOne('App\Models\Fuel');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
}
