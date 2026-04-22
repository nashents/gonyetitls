<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripStatus extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;
     public function user(){
        return $this->belongsTo('App\Models\User');
    }
     public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }

    protected $fillable = [
        'user_id',
        'trip_id',
        'status',
        'date',
        'description',
    ];
}
