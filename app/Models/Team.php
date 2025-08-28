<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model implements Auditable
{
    use HasFactory,SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function shifts(){
        return $this->hasMany('App\Models\Shift');
    }
    public function employees(){
        return $this->belongsToMany('App\Models\Employee');
    }

    protected $fillable = [
        'user_id',
        'name',
        'status',
    ];
}
