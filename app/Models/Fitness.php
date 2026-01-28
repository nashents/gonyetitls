<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fitness extends Model  implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function fitnesses(){
        return $this->belongsToMany('App\Models\Fitness');
    }
    public function recipients(){
        return $this->belongsToMany('App\Models\Recipient');
    }
    public function reminder_item(){
        return $this->belongsTo('App\Models\ReminderItem');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }

    protected $casts = [
        'expires_at'               => 'datetime',
        'first_reminder_at'        => 'datetime',
        'second_reminder_at'       => 'datetime',
        'third_reminder_at'        => 'datetime',
        'issued_at'          => 'datetime',

        'first_reminder_at_status' => 'boolean',
        'second_reminder_at_status'=> 'boolean',
        'third_reminder_at_status' => 'boolean',
        'closed'                   => 'boolean',
        'cc'                       => 'boolean',
    ];

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'horse_id',
        'trailer_id',
        'name',
        'issued_at',
        'expires_at',
        'reminder_at',
        'status',
        'first_reminder_at',
        'second_reminder_at',
        'third_reminder_at',
        'first_reminder_at_status',
        'second_reminder_at_status',
        'third_reminder_at_status',
        'closed',
        'cc',
    ];

    

}
