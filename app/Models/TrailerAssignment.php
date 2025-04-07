<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrailerAssignment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function mileage(){
        return $this->hasOne('App\Models\Mileage');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }

    protected $fillable =[
        'user_id',
        'vehicle_id',
        'horse_id',
        'trailer_id',
        'transporter_id',
        'starting_odometer',
        'ending_odometer',
        'start_date' ,
        'end_date',
        'comments',
        'status' ,
    ];

    
}
