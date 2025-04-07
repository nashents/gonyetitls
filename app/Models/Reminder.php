<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reminder extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    

    public function recipients(){
        return $this->belongsToMany('App\Models\Recipient');
    }

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function reminder_item(){
        return $this->belongsTo('App\Models\ReminderItem');
    }
}

