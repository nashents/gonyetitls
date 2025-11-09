<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TyreAssignment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    protected $appends = ['travelled_km', 'remaining_km', 'remaining_pct'];

    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function mileage(){
        return $this->hasOne('App\Models\Mileage');
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function ticket(){
        return $this->belongsTo('App\Models\Ticket');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function ticket_inventory(){
        return $this->belongsTo('App\Models\TicketInventory');
    }
     public function checklist_results(){
        return $this->hasMany('App\Models\ChecklistResult');
    }
    public function tyre(){
        return $this->belongsTo('App\Models\Tyre');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    public function tyre_dispatch(){
        return $this->hasOne('App\Models\TyreDispatch');
    }

    protected $fillable=[
        'user_id',
        'vehicle_id',
        'horse_id',
        'trailer_id',
        'tyre_id',
        'starting_odometer',
        'ending_odometer',
        'position',
        'axle',
        'status',
    ];

      public function getTravelledKmAttribute()
    {
        $start = $this->starting_odometer;
        if (is_null($start)) {
            return null;
        }

        // If removed, freeze distance at removal; else use current horse odometer.
        $end = $this->ending_odometer ?? optional($this->horse)->mileage;

        if (is_null($end)) {
            return null;
        }

        return max(0, (int)$end - (int)$start);
    }

    public function getRemainingKmAttribute()
    {
        $std = optional($this->tyre)->life_span;
        $travelled = $this->travelled_km;

        if (is_null($std) || is_null($travelled)) {
            return null;
        }

        return max(0, (int)$std - (int)$travelled);
    }

    public function getRemainingPctAttribute()
    {
        $std = optional($this->tyre)->life_span;
        $rem = $this->remaining_km;

        if (empty($std) || is_null($rem)) {
            return null;
        }

        return round(($rem / $std) * 100, 1); // e.g., 63.4
    }
}
