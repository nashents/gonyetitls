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
}
