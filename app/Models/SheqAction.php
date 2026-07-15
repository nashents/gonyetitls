<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqAction extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function verified_by(){
        return $this->belongsTo('App\Models\User','verified_by_id');
    }
    public function actionable(){
        return $this->morphTo();
    }
    public function isOverdue(){
        return $this->due_date
            && !in_array($this->status, ['completed','verified'])
            && \Carbon\Carbon::parse($this->due_date)->isPast();
    }
}
