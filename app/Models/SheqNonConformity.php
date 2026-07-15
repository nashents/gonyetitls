<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqNonConformity extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    protected $table = 'sheq_non_conformities';

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function raised_by(){
        return $this->belongsTo('App\Models\Employee','raised_by_id');
    }
    public function actions(){
        return $this->morphMany('App\Models\SheqAction','actionable');
    }
}
