<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Driver extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function brokers(){
        return $this->belongsToMany('App\Models\Broker');
    }
       public function shifts(){
        return $this->hasMany('App\Models\Shift');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function journal_entry_lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }
   
    public function fitnesses(){
        return $this->hasMany('App\Models\Fitness');
    }
    public function incidents(){
        return $this->hasMany('App\Models\Incident');
    }
    public function compliances(){
        return $this->hasMany('App\Models\Compliance');
    }
    public function trainings(){
        return $this->hasMany('App\Models\Training');
    }
    public function gate_passes(){
        return $this->hasMany('App\Models\GatePass');
    }
    public function emails(){
        return $this->hasMany('App\Models\Email');
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
    public function recoveries(){
        return $this->hasMany('App\Models\Recovery');
    }
    public function bills(){
        return $this->hasMany('App\Models\Bill');
    }
    public function driver_allowances(){
        return $this->hasMany('App\Models\AllowanceDriver');
    }
    public function transport_orders(){
        return $this->hasMany('App\Models\TransportOrder');
    }
    public function payments(){
        return $this->hasMany('App\Models\Payment');
    }
    public function trip_locations(){
        return $this->hasMany('App\Models\TripLocation');
    }
    public function driver_transporters(){
        return $this->belongsToMany('App\Models\Transporter');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function bookings(){
        return $this->hasMany('App\Models\Booking');
    }
    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }
    public function cash_flows(){
        return $this->hasMany('App\Models\CashFlow');
    }
    public function receipts(){
        return $this->hasMany('App\Models\Receipt');
    }
    public function trip_returns(){
        return $this->hasMany('App\Models\TripReturn');
    }
    public function assignments(){
        return $this->hasMany('App\Models\Assignment');
    }
    public function fuels(){
        return $this->hasMany('App\Models\Fuel');
    }

 protected $fillable=[
    'user_id',
    'employee_id',
    'driver_number',
    'license_number',
    'passport_number',
    'class',
    'experience',
    'reference',
    'reference_phonenumber',
 ];
}
